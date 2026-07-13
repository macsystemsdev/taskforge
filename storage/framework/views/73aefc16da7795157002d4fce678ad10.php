<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Organization Invitation
    </title>

</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f4f4f5;
        font-family: Arial, sans-serif;
    ">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 20px;">

        <tr>

            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0"
                    style="
                        max-width: 600px;
                        background: #ffffff;
                        border-radius: 12px;
                        overflow: hidden;
                    ">

                    
                    <tr>

                        <td
                            style="
                                background: #18181b;
                                padding: 32px;
                                color: white;
                                text-align: center;
                            ">

                            <h1
                                style="
                                    margin: 0;
                                    font-size: 28px;
                                ">
                                TaskForge
                            </h1>

                            <p
                                style="
                                    margin-top: 8px;
                                    color: #d4d4d8;
                                ">
                                Organization Invitation
                            </p>

                        </td>

                    </tr>

                    
                    <tr>

                        <td style="padding: 40px;">

                            <h2
                                style="
                                    margin-top: 0;
                                    color: #18181b;
                                ">
                                You've Been Invited
                            </h2>

                            <p
                                style="
                                    color: #52525b;
                                    line-height: 1.6;
                                ">
                                <?php echo e($invitation->inviter->name); ?>

                                invited you to collaborate inside
                                <strong>
                                    <?php echo e($invitation->organization->name); ?>

                                </strong>.
                            </p>

                            <p
                                style="
                                    color: #52525b;
                                    line-height: 1.6;
                                ">
                                Your assigned role:
                                <strong>
                                    <?php echo e(ucfirst($invitation->role)); ?>

                                </strong>
                            </p>

                            
                            <table cellpadding="0" cellspacing="0" style="margin-top: 32px;">

                                <tr>

                                    <td>

                                        <a href="<?php echo e(route('invitations.accept', $invitation->token)); ?>"
                                            style="
                                                display: inline-block;
                                                background: #18181b;
                                                color: white;
                                                text-decoration: none;
                                                padding: 14px 24px;
                                                border-radius: 8px;
                                                font-weight: bold;
                                            ">
                                            Accept Invitation
                                        </a>

                                    </td>

                                    <td width="12"></td>

                                    <td>

                                        <a href="<?php echo e(route('invitations.reject.form', $invitation->token)); ?>"
                                            style="
                                                display: inline-block;
                                                background: #dc2626;
                                                color: white;
                                                text-decoration: none;
                                                padding: 14px 24px;
                                                border-radius: 8px;
                                                font-weight: bold;
                                            ">
                                            Reject Invitation
                                        </a>

                                    </td>

                                </tr>

                            </table>

                            
                            <p
                                style="
                                    margin-top: 32px;
                                    color: #71717a;
                                    font-size: 14px;
                                ">
                                This invitation expires on
                                <?php echo e($invitation->expires_at->format('M d, Y H:i')); ?>.
                            </p>

                            
                            <p
                                style="
                                    margin-top: 24px;
                                    color: #71717a;
                                    font-size: 13px;
                                    line-height: 1.6;
                                ">
                                If the buttons above do not work,
                                copy and paste this URL into your browser:
                            </p>

                            <p
                                style="
                                    word-break: break-all;
                                    font-size: 13px;
                                    color: #2563eb;
                                ">
                                <?php echo e(route('invitations.accept', $invitation->token)); ?>

                            </p>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>
<?php /**PATH D:\Code\taskforge\resources\views/emails/organization_invitation.blade.php ENDPATH**/ ?>