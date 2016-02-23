@if(AccountApprovalList::isVerfiable($Account->AccountID) == false || $Account->VerificationStatus != Account::VERIFIED)
    <div  class=" toast-container-fix toast-top-full-width">
        <div class="toast toast-error" style="">
            <div class="toast-title">Error</div>
            <div class="toast-message">
                @if($Account->VerificationStatus == Account::VERIFIED)
                    Awaiting Account Verification Documents Upload.
                @elseif($Account->VerificationStatus == Account::NOT_VERIFIED || $Account->VerificationStatus == Account::PENDING_VERIFICATION)
                    Account Pending Verification.
                @endif
            </div>
        </div>
    </div>
@endif
@if(Account::AuthIP($Account))
    <div  class=" toast-container-fix toast-top-full-width">
        <div class="toast toast-warning" style="">
            <div class="toast-title">Warning Message</div>
            <div class="toast-message">
                No IPs are provided under authentication.
            </div>
        </div>
    </div>
@endif