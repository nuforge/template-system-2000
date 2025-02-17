<h1>Forgot Password</h1>

{if:success}
<p>An email has been sent to the address provided. Please follow those instruction to complete the process.</p>
{else:}
<p>Please enter your email address and instructions on how to change your password.</p>
<form method="post">
    <table class="formtable">
        <tr>
            <th>Email Address</th>
            <td><input type="text" name="reset_email" id="reset_email" size="64"/></td>
        </tr>
        <tr>
            <th colspan="2"><input type="submit" name="submit" value="Send Password"/></th>
        </tr>
    </table>
</form>

{end:}