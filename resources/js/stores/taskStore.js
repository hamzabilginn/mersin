import { defineStore } from 'pinia';
import { localDb, SyncManager } from '@/services/sync';
import { useAuthStore } from './authStore';

export const useTaskStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        bottlenecks: null,
        loading: false
    }),

    actions: {
        async loadTasks() {
            this.loading = true;
            const authStore = useAuthStore();
            
            try {
                if (authStore.isOnline) {
                    const res = await fetch('/api/tasks');
                    const data = await res.json();
                    this.tasks = data;
                    // Cache tasks locally
                    await localDb.clear('tasks');
                    await localDb.putAll('tasks', data);
                } else {
                    this.tasks = await localDb.getAll('tasks');
                }
            } catch (err) {
                this.tasks = await localDb.getAll('tasks');
            } finally {
                this.loading = false;
            }
        },

        async createTask(taskData) {
            const authStore = useAuthStore();
            const payload = {
                ...taskData,
                tech_office_id: authStore.currentUser.id,
            };

            if (authStore.isOnline) {
                try {
                    const res = await fetch('/api/tasks', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        const newTask = await res.json();
                        this.tasks.unshift(newTask);
                        await localDb.put('tasks', newTask);
                        return newTask;
                    }
                } catch (err) {
                    console.error('Task creation online failed, queueing offline:', err);
                }
            }

            // Offline / Fallback: Optimistic Save
            const tempId = 'temp-' + Date.now();
            const homUser = authStore.users.find(u => u.id == taskData.hom_id);
            const scUser = authStore.users.find(u => u.role === 'sc');
            const pmUser = authStore.users.find(u => u.role === 'pm');

            const tempTask = {
                id: tempId,
                zzz_code: taskData.zzz_code,
                tow: taskData.tow,
                stow: taskData.stow,
                sstow: taskData.sstow,
                planned_qty: taskData.planned_qty,
                planned_man_day: taskData.planned_man_day,
                status: 'assigned',
                tech_office_id: authStore.currentUser.id,
                hom_id: taskData.hom_id,
                sc_id: scUser ? scUser.id : null,
                pm_id: pmUser ? pmUser.id : null,
                due_date: taskData.due_date,
                techOffice: authStore.currentUser,
                hom: homUser,
                sc: scUser,
                pm: pmUser,
                logs: [{
                    id: Date.now(),
                    new_status: 'assigned',
                    comment: 'Plan oluşturuldu (Çevrimdışı/Yerel)',
                    user: authStore.currentUser,
                    created_at: new Date().toISOString()
                }],
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
            };

            this.tasks.unshift(tempTask);
            await localDb.put('tasks', tempTask);
            await SyncManager.queueRequest('/api/tasks', 'POST', payload);
            await authStore.updateSyncCount();
            return tempTask;
        },

        async updateStatus(taskId, updateData) {
            const authStore = useAuthStore();
            const payload = {
                ...updateData,
                user_id: authStore.currentUser.id,
            };

            // Find task in local state and update optimistically
            const taskIndex = this.tasks.findIndex(t => t.id == taskId);
            let oldStatus = 'draft';
            if (taskIndex !== -1) {
                const task = this.tasks[taskIndex];
                oldStatus = task.status;
                task.status = updateData.status;
                
                if (updateData.fact_qty !== undefined && updateData.fact_qty !== null && updateData.fact_qty !== '') task.fact_qty = updateData.fact_qty;
                if (updateData.fact_man_day !== undefined && updateData.fact_man_day !== null && updateData.fact_man_day !== '') task.fact_man_day = updateData.fact_man_day;
                if (updateData.overtime !== undefined && updateData.overtime !== null && updateData.overtime !== '') task.overtime = updateData.overtime;
                if (updateData.comment !== undefined && updateData.comment !== null && updateData.comment !== '') task.comment = updateData.comment;

                if (!task.logs) task.logs = [];
                task.logs.unshift({
                    id: Date.now(),
                    old_status: oldStatus,
                    new_status: updateData.status,
                    comment: updateData.comment || '',
                    user: authStore.currentUser,
                    created_at: new Date().toISOString()
                });
                await localDb.put('tasks', task);
            }

            if (authStore.isOnline) {
                try {
                    const res = await fetch(`/api/tasks/${taskId}/status`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        const updatedTask = await res.json();
                        if (taskIndex !== -1) {
                            this.tasks[taskIndex] = updatedTask;
                            await localDb.put('tasks', updatedTask);
                        }
                        return updatedTask;
                    } else {
                        const errorData = await res.json();
                        alert("Hata: " + errorData.error);
                        // Revert optimistic update?
                        return false;
                    }
                } catch (err) {
                    console.error('Task status update failed online, queueing:', err);
                }
            }

            // Save to sync queue if offline or failed
            await SyncManager.queueRequest(`/api/tasks/${taskId}/status`, 'PUT', payload);
            await authStore.updateSyncCount();
            return this.tasks[taskIndex]; // return optimistic task
        },

        async loadBottlenecks() {
            const authStore = useAuthStore();
            if (authStore.isOnline) {
                try {
                    const res = await fetch('/api/bottlenecks');
                    this.bottlenecks = await res.json();
                } catch (err) {
                    console.error(err);
                }
            } else {
                // Offline fallback math
                const today = new Date().toISOString().split('T')[0];
                const delayed = this.tasks.filter(t => t.status !== 'approved' && t.due_date < today);
                const stuck = this.tasks.filter(t => ['pending_sc', 'pending_pm'].includes(t.status));
                
                const summary = {
                    total: this.tasks.length,
                    draft: this.tasks.filter(t => t.status === 'draft').length,
                    assigned: this.tasks.filter(t => t.status === 'assigned').length,
                    in_progress: this.tasks.filter(t => t.status === 'in_progress').length,
                    pending_sc: this.tasks.filter(t => t.status === 'pending_sc').length,
                    pending_pm: this.tasks.filter(t => t.status === 'pending_pm').length,
                    approved: this.tasks.filter(t => t.status === 'approved').length,
                    rejected: this.tasks.filter(t => t.status === 'rejected').length,
                    delayed_count: delayed.length
                };

                const chart_data = {
                    assigned: summary.assigned,
                    in_progress: summary.in_progress,
                    pending_sc: summary.pending_sc,
                    pending_pm: summary.pending_pm,
                    approved: summary.approved,
                    rejected: summary.rejected
                };

                this.bottlenecks = {
                    delayed_tasks: delayed,
                    stuck_tasks: stuck,
                    chart_data,
                    summary
                };
            }
        }
    }
});
