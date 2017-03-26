<?php
return [
    'my_acc_title'              => 'My account',
    'my_acc_bread'              => 'Settings',
    'email_address'             => 'E-mail address',
    'email_address_new'         => 'New e-mail addresse',
    'email_blacklist'           => 'Block all e-mails to this account',
    'email_unconfirmed'         => 'unconfirmed',
    'pwd_confirm'               => 'Confirm password',
    'pwd_change'                => 'Change password',
    'pwd_change_hint'           => 'Empty = leave unchanged',
    'name'                      => 'Name',
    'err_pwd_different'         => 'The two passwords are not the same.',
    'err_pwd_length'            => 'The password has to be at least %MINLEN% characters long.',
    'err_user_acode_notfound'   => 'User not found / invalid code',
    'err_user_notfound'         => 'The user account %USER% was not found.',
    'err_code_wrong'            => 'The given code is invalid.',
    'pwd_recovery_sent'         => 'A password recovery e-mail has been sent.',
    'welcome'                   => 'Welcome!',
    'err_email_acc_notfound'    => 'There is not account with this e-mail address...?',
    'err_invalid_email'         => 'The given e-mail address is invalid',
    'err_unknown'               => 'An unknown error occurred',
    'err_unknown_ww_repeat'     => 'An unknown error occurred.',
    'err_no_recovery'           => 'No recovery request was sent within the last 24 hours.',
    'err_change_toolong'        => 'The request is too old; please request another change request and confirm the e-mail within 24 hours',
    'recover_mail_title'        => 'Antragsgrün: Password recovery',
    'recover_mail_body'         => "Hi!\n\nYou requested a password recovery. " .
        "To proceed, please open the following page and enter the new password:\n\n%URL%\n\n" .
        "Or enter the following code on the recovery page: %CODE%",
    'err_recover_mail_sent'     => 'There already has been a recovery e-mail sent within the last 24 hours.',
    'err_emailchange_mail_sent' => 'You already requested an e-mail change within the last 24 hours.',
    'err_emailchange_notfound'  => 'No e-mail change was requested or it is already being implemented.',
    'err_emailchange_flood'     => 'To prevent e-mail flooding, there needs to be a gap of at least 5 minutes between sending two e-mails',
    'emailchange_mail_title'    => 'Confirm new e-mail address',
    'emailchange_mail_body'     => "Hi!\n\nYou requested to change the e-mail address. " .
        "To proceed, please open the following page:\n\n%URL%\n\n",
    'emailchange_sent'          => 'A confirmation e-mail has been sent to this address. ' .
        'Please open the included link to change the address.',
    'emailchange_done'          => 'The e-mail address has been changed.',
    'emailchange_requested'     => 'E-mail address requested (not confirmed yet)',
    'emailchange_call'          => 'change',
    'emailchange_resend'        => 'New confirmation mail',
    'del_title'                 => 'Delete account',
    'del_explanation'           => 'Here you can delete this account. You will not receive any more e-mails, no login will be possible after this.
        Your e-mail address, name and contact data will be deleted.<br>
        Motions and amendments you submitted will remain visible. To withdraw already submitted motions, please contact the relevant convention administrators.',
    'del_confirm'               => 'Confirm delete',
    'del_do'                    => 'Delete',
    'noti_greeting'             => 'Hi %NAME%,',
    'noti_bye'                  => "Kind regards,\n   The Antragsgrün Team\n\n--\n\n" .
        "If you do not want to receive any more e-mails, you can unsubscribe here:\n",
    'noti_new_motion_title'     => '[Antragsgrün] New motion:',
    'noti_new_motion_body'      => "A new motion was submitted:\nConsultation: %CONSULTATION%\n" .
        "Name: %TITLE%\nLink: %LINK%",
    'noti_new_amend_title'      => '[Antragsgrün] New amendment for %TITLE%',
    'noti_new_amend_body'       => "A new amendment was submitted:\nConsultation: %CONSULTATION%\n" .
        "Motion: %TITLE%\nLink: %LINK%",
    'noti_amend_mymotion'       => "A new amendment has been published to your motion:\nConsultation: %CONSULTATION%\n" .
        "Motion: %TITLE%\nLink: %LINK%\n%MERGE_HINT%",
    'noti_amend_mymotion_merge' => "\nIf you agree with this amendment, you can adopt the changes (\"Adopt changes into motion\" in the sidebar)",
    'noti_new_comment_title'    => '[Antragsgrün] New comment to %TITLE%',
    'noti_new_comment_body'     => "%TITLE% was commented:\n%LINK%",
    'acc_grant_email_title'     => 'Antragsgrün access',
    'acc_grant_email_userdata'  => "E-mail / username: %EMAIL%\nPassword: %PASSWORD%",


    'login_title'             => 'Login',
    'login_username_title'    => 'Login using username/password',
    'login_create_account'    => 'Create a new account',
    'login_username'          => 'E-mail address / username',
    'login_email_placeholder' => 'Your e-mail address',
    'login_password'          => 'Password',
    'login_password_rep'      => 'Password (Confirm)',
    'login_create_name'       => 'Your name',
    'login_btn_login'         => 'Log In',
    'login_btn_create'        => 'Create',
    'login_forgot_pw'         => 'Forgot your password?',
    'login_openid'            => 'OpenID login',
    'login_openid_url'        => 'OpenID URL',

    'access_denied_title' => 'No access',
    'access_denied_body'  => 'You don\' have access to this site. If you think this is an error, please contact the site administrator (as stated in the imprint).',
];
