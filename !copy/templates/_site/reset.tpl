<h1>Reset Password</h1>

{if:success}
<p>Your password has been reset. <a href="/login.html" title="Log into {page[sitename]}">Log in here.</a></p>
{else:}
<p>Please enter your email address and choose a new password.</p>
<form method="post" name="reset_form">
    <table class="formtable">
        <tr>
            <th>Email Address</th>
            <td><input type="text" name="reset_email" id="reset_email" size="64"/></td>
        </tr>
        <tr>
            <th>New Password</th>
            <td><input type="password" name="new_password" id="new_password" size="32"/></td>
        </tr>
        <tr>
            <th>Confirm Address</th>
            <td><input type="password" name="confirm_password" id="confirm_password" size="32"/></td>
        </tr>
        <tr>
            <th colspan="2"><input type="submit" name="submit" value="Reset Password"/></th>
        </tr>
    </table>
    <input type="hidden" name="code" value="{code}"/>
</form>

{end:}