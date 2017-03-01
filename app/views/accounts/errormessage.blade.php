@if(AccountApprovalList::isVerfiable($Account->AccountID) == false || $Account->VerificationStatus != Account::VERIFIED)
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger">
                @if($Account->VerificationStatus == Account::VERIFIED)
                    Awaiting Account Verification Documents Upload.
                @elseif($Account->VerificationStatus == Account::NOT_VERIFIED )
                    Account Pending Verification.
                @endif
            </div>
        </div>
    </div>
@endif
@if($Account->Blocked == 1)
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-danger">
                Account Blocked.
            </div>
        </div>
    </div>
@endif
@if(Account::AuthIP($Account) && ($Account->IsCustomer==1 || $Account->IsVendor==1))
    <div  class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                No IPs are setup under authentication rule.
            </div>
        </div>
    </div>
@endif