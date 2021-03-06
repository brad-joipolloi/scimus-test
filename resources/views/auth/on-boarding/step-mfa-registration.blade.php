@extends('layouts.onboarding')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-around">
            <span class="text-muted">
                Create Password
            </span>
            <span class="text-dark">
                Setup Authenticator
            </span>
        </div>

        <div class="card-body">
            <form action="{{ route('user.onboarding.mfa.process', [$token, encrypt($user->email)]) }}" method="post" class="registrationMFAStep">
                @csrf

                <div class="alert alert-primary">
                    <p>
                        <strong>Authenticator App</strong>
                    </p>
                    <p>
                        The kiosk management system makes use of a multi factor authentication system.
                        This means that every time you log into the system, you will be asked to enter a 6 digit code.
                        This code is generated by an application that can be on your phone, or your computer.
                    </p>
                    <p>
                        To register this site with your authenticator application please either copy, or scan the QR
                        code shown below.
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <img src="data:image/png;base64,{{ $qr }}" class="w-100">
                    </div>
                    <div class="col-md-7 pt-3">
                        <div class="form-group">
                            <label for="mfa-secret">Authenticator Secret</label>
                            <input class="form-control" value="{{ $mfaSecret }}" id="mfa-secret" disabled>
                        </div>

                        <div class="form-group">
                            <strong>
                                Please ensure you have either scanned the qr code to the left,
                                or made a copy of the code above.
                            </strong>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="confirmation" class="custom-control-input my-auto makesButtonClickable" id="confirm-mfa-copied" data-target=".submitsMfaRegistrationForm">
                                <label class="custom-control-label" for="confirm-mfa-copied">
                                    I confirm I have configured my authenticator app as required and that this is the only time I will see this information.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="card-footer text-right">
            <button type="button" class="btn btn-primary submitsForm submitsMfaRegistrationForm" data-target="registrationMFAStep" disabled>
                Continue to login
            </button>
        </div>
    </div>
@endsection
