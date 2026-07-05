# URGENT CORRECTION INSTRUCTIONS FOR AI AGENT

**ATTENTION: THE CURRENT ARCHITECTURE YOU BUILT IS INCORRECT FOR THE CASE STUDY.**
You have built a generic "Task Management & Chat" application (`Task`, `Message` models). This is an absolute failure for the "Workforce Execution Platform" Case Study. The domain is Construction/Nuclear Field Operations, not a simple To-Do app. 

You MUST scrap the generic Task models and implement the exact domain models described below.

## 1. Incorrect Database Models
**DELETE** the following tables/models: `tasks`, `messages`, `task_logs`, `notifications`.
**CREATE** the following tables/models:
- `wbs_items` (Fields: `zzz_code` (Primary Key), `tow_code`, `stow_code`, `sstow_code`, `unit`, `type_of_code`)
- `daily_plans` (Fields: `id`, `report_date`, `kkk_code`, `project_code`, `zzz_code` (Foreign Key), `planned_qty`, `planned_manday`, `submitted_by`, `assigned_hom`, `crew_region`, `status`)
- `daily_facts` (Fields: `id`, `plan_id` (Foreign Key), `fact_qty`, `fact_manday`, `overtime`, `comment`, `zzz_detail`, `is_not_started`, `status`, `synced_at`)
- `approvals` (Fields: `id`, `fact_id`, `approved_by`, `role`, `action`, `comment`)

## 2. Missing Roles & RBAC
You must implement strict role-based access control (RBAC):
- **Tech Office (`submitted_by`):** Creates `daily_plans` (T-1 step).
- **Head of Master (`assigned_hom`):** Assigned to a specific `kkk_code`. They view plans and create `daily_facts` (T0 step).
- **Site Chief:** Approves facts for their `crew_region`.
- **Project Manager:** Final approver.

## 3. Critical Business Rules You Missed
- **ZZZ Code:** The `wbs_items` table generates a `zzz_code` based on Excel (WBS_V3) data. This is the heart of the system.
- **The "-1" Rule:** When creating a `DailyFact`, if the task did not start or finish, the user enters `fact_qty = -1`. In this specific scenario, the `comment` field MUST be mandatory in the backend validation.

## 4. Workflows
- T-1: Tech office assigns a plan.
- T0: HoM enters actual facts at the end of the day.
- Offline Sync: Assume the mobile app works offline. The `daily_facts` API endpoint must support bulk inserts with an `Idempotency-Key` to prevent double database entries when the internet connects.

**ACTION REQUIRED:**
Read these instructions, delete your generic models, and rebuild the migrations, models, and controllers exactly matching the "Workforce Execution Platform" domain. This is for a Senior Architect interview. Precision is mandatory.
