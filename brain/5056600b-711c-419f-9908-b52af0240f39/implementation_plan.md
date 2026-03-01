# Revenue Workflow Completion - Final Stages

Implementing the final stages of the revenue cycle: Cashier receipt with OTP verification and the Manager's "Pending Orders" & "Variance Alert" monitoring systems.

## User Review Required

> [!IMPORTANT]
> **OTP Verification**: The system will generate a 6-digit OTP when the Accountant initiates a transfer to the Cashier. The Cashier must enter this code (provided by the physical Accountant) to confirm receipt.
> 
> **Delay Alerts**: Insurance/Charity approvals delayed beyond 2 hours will be flagged in red on the Manager's dashboard.

## Proposed Changes

---

### Database Schema
Updating the `invoices` table to track the handover to the cashier.

#### [MODIFY] [2026_02_24_225000_add_cashier_stages_to_invoices.php](file:///d:/Creatify/hr-hospital-v2/database/migrations/2026_02_24_225000_add_cashier_stages_to_invoices.php) [NEW]
- Add `cashier_otp` (string, nullable)
- Add `cashier_id` (foreignId to users, nullable)
- Add `cashier_received_at` (timestamp, nullable)

---

### Cashier Workflow
Creating a dedicated interface for cashiers to receive funds and verify them using OTP.

#### [NEW] [CashierWorkflowController.php](file:///d:/Creatify/hr-hospital-v2/app/Http/Controllers/CashierWorkflowController.php)
- `index()`: Display invoices marked as `ready_for_deposit`.
- `initiateTransfer(Invoice $invoice)`: Generate OTP and mark invoice.
- `receive(Request $request, Invoice $invoice)`: Verify OTP and finalize the transfer.

#### [NEW] [cashier/index.blade.php](file:///d:/Creatify/hr-hospital-v2/resources/views/revenue/cashier/index.blade.php)
- List of awaiting invoices.
- Receipt confirmation interface with OTP input.

---

### Manager Monitoring Features
Implementing the "Pending Orders" detector and "Variance Alert" logic.

#### [MODIFY] [RevenueWorkflowController.php](file:///d:/Creatify/hr-hospital-v2/app/Http/Controllers/RevenueWorkflowController.php)
- `pendingDetector()`: Fetches pending insurance/charity approvals with age calculation.

#### [NEW] [revenue/pending-detector.blade.php](file:///d:/Creatify/hr-hospital-v2/resources/views/revenue/pending-detector.blade.php)
- Real-time countdown timers for pending requests.
- Color-coded urgency (Red for > 2 hours).

---

### Variance Alert & Enforcement
Ensuring every riyal is collected or documented.

#### [MODIFY] [InvoiceController.php](file:///d:/Creatify/hr-hospital-v2/app/Http/Controllers/InvoiceController.php)
- Add validation logic to `store()` and `update()` to check `total_items_price` vs `payments_sum`.
- Require `approval_id` or `discount_reason` if there is a negative variance.

---

## Verification Plan

### Automated Tests
- Run `php artisan migrate` to verify schema changes.
- Manual testing of the OTP generation/verification loop.

### Manual Verification
1. **Cashier Stage**: Confirm an invoice as Accountant -> Log in as Cashier -> Enter OTP -> Verify status change.
2. **Pending Detector**: Create an approval request -> Verify it appears in Manager view -> Check color change after 2 hours (can be mocked by changing `created_at`).
3. **Variance Alert**: Try to save a cash invoice with a payment less than the total -> Verify the system blocks it and requests a reason/discount.
