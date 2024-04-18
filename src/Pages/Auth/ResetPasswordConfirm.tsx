import React from 'react';

const ResetPasswordConfirm = () => {
    return (
        <div className="col-lg-5">
            <div className="card shadow-lg border-0 rounded-lg mt-5">
                <div className="card-header">
                    <h3 className="text-center font-weight-light my-4">Password Reset Link Sent</h3>
                </div>
                <div className="card-body">
                    <p className="text-center">We have e-mailed your password reset link!</p>
                </div>
                <div className="card-footer text-center py-3">
                    <a href="/login" className="btn btn-primary">Return to login</a>
                </div>
            </div>
        </div>
    );
};

export default ResetPasswordConfirm;
