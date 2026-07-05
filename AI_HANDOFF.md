# AI HANDOFF DOCUMENT: WORKFORCE EXECUTION PLATFORM (ICN CASE STUDY)

**TO THE NEXT AI AGENT:**
Hello! I am the AI agent that developed the initial architecture, presentations, and the MVP for this Case Study. Our user (Mustafa Hazar Bilgin) is preparing for an important technical architect interview. You need to understand the exact context, decisions, and domain rules we established so you don't contradict the established narrative.

## 1. The Core Objective
The company wants to digitize their daily field operations (currently Excel-based) for a massive construction/nuclear site. 
The system tracks planned vs. actual work, productivity, and labor using a specific hiearchy: **Project -> ToW -> SToW -> SSToW**.
The unique identifier for any task line is the **ZZZ Code**.

## 2. Roles & Authentication (RBAC)
- **Tech Office (Submitted By):** Creates the Daily Plan at T-1 for their assigned `crew_region`.
- **Head of Master (HoM):** Assigned to a specific Location (`kkk_code`). They create crews, assign workers, and enter actuals at the end of the day.
- **Site Chief (SC):** Approves/Rejects tasks for their specific `crew_region`.
- **Project Manager (PM):** Final approver for the entire project.

## 3. The Business Workflow
- **T-1 (Planning):** Tech office creates a draft plan, sets Planned Qty & Man-Day.
- **T0 (Execution):** HoM receives it on their mobile app, assigns crew.
- **End of Day (Fact Entry):** HoM enters Fact Qty, Fact Man-Day, Overtime, and ZZZ Detail.
- **CRITICAL BUSINESS RULE:** If the task didn't start or finish (`fact_qty = -1`), the user MUST enter a `comment`.
- **Approval:** Auto-sync -> Pending SC -> Pending PM -> Approved (Immutable).

## 4. The Architecture (Theoretical vs MVP)
**CRITICAL CONTEXT:** You will see a contradiction between the Presentation and the Codebase. **THIS IS INTENTIONAL.**
- **The Theoretical Architecture (In the Presentation):** We proposed an Enterprise stack: .NET Core 8, React, Flutter, PostgreSQL, Redis, MassTransit/RabbitMQ, Docker On-Premise. This is what the user will *present* to the jury.
- **The Codebase MVP (`workforce-mvp` & `Workforce_Execution_MVP.html`):** Because we had limited time, we built a rapid prototype using **Laravel 11, SQLite, and Vue 3**. The user will present this to the jury as a "Rapid Prototyping Showcase" to demonstrate UI/UX and Offline Data Sync concepts. **Do not force the user to rewrite this in .NET.** Accept Laravel as the simulation tool.

## 5. The Offline-First Scenario
Since construction sites lack internet, the mobile app (Flutter in theory, Vue prototype in practice) works 100% offline.
- We use an `Idempotency-Key` (a unique local UUID generated when offline) to prevent double-insertions when the network is restored and the app bulk-syncs data.

## 6. Your Goal
Support the user with any enhancements to the Laravel MVP or the presentation. Do not change the core narrative. Reinforce the Senior Architect persona. Ensure any code you write respects the `fact_qty = -1` rule and the ZZZ Code logic.

Good luck!
- Agent Antigravity
