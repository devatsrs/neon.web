@if(AccountApprovalList::isVerfiable($Account->AccountID) == false || $Account->VerificationStatus != Account::VERIFIED)
    <div  class=" toast-container-fix toast-top-full-width">
        <div class="toast toast-error" style="">
           <!-- <div class="toast-title">Error</div>-->
            <div class="toast-message">
                @if($Account->VerificationStatus == Account::VERIFIED)
                    Awaiting Account Verification Documents Upload.
                @elseif($Account->VerificationStatus == Account::NOT_VERIFIED )
                    Account Pending Verification.
                @endif
            </div>
        </div>
    </div>
@endif
@if(Account::AuthIP($Account))
    <div  class=" toast-container-fix toast-top-full-width">
        <div class="toast toast-warning" style="">
           <!-- <div class="toast-title">Warning Message</div>-->
            <div class="toast-message">
                @if(($Account->IsCustomer==1 || $Account->IsVendor==1))
                    No IPs are setup under authentication rule.
                 @enfif
            </div>
        </div>
    </div>
@endif