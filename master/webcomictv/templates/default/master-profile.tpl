<h1>Update Profile</h1>
{if:success}
<p>You have successfully registered. Please check your email shortly for your login information. If you haven't received it within a few minutes be sure to check your spam filters.</p>
{end:}
<p>Registering is free and easy.  Just fill out the forms below and you'll have instant access to our free members area.</p>
<p>If you have not received your account information within a few minutes, please be sure to check your spam folder.</p>
{if:status_message}{status_message:h}{end:}
<div class="round_dark">
    <form name="register" method="post">
        <table class="formtable">
            <tr>
                <th>Email</th>
                <td><input name="reg_email" type="text" id="reg_email" size="64" maxlength="100"></td>
            </tr>
            <tr>
                <th>Confirm Email</th>
                <td><input name="reg_cemail" type="text" id="reg_cemail" size="64" maxlength="100"></td>
            </tr>
            <tr>
                <th>First Name<br></th>
                <td><input name="reg_firstname" type="text" id="reg_firstname" size="32" maxlength="64">
                </td>
            </tr>
            <tr>
                <th>Last Name<br></th>
                <td><input name="reg_lastname" type="text" id="reg_lastname" size="32" maxlength="64">
                </td>
            </tr>
            <tr>
                <th>Date of Birth</th>
                <td><select name="reg_birth_month" id="reg_birth_month">
                        <option value="01">1 - January</option>
                        <option value="02">2 - February</option>
                        <option value="03">3 - March</option>
                        <option value="04">4 - April</option>
                        <option value="05">5 - May</option>
                        <option value="06">6 - June</option>
                        <option value="07">7 - July</option>
                        <option value="08">8 - August</option>
                        <option value="09">9 - September</option>
                        <option value="10">10 - October</option>
                        <option value="11">11 - November</option>
                        <option value="12">12 - December</option>
                    </select>
                    <select name="reg_birth_day" id="reg_birth_day">
                    </select>
                    <select name="reg_birth_year" id="reg_birth_year">
                    </select></td>
            </tr>
            <tr>
                <th width="25%">Gender</th>
                <td><select name="reg_gender" id="reg_gender">
                        <option value="m">Male</option>
                        <option value="f">Female</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td align="right"><input type="hidden" name="cmd" value="submit">
                    <input type="submit" name="Submit" value="Submit"></td>
            </tr>
        </table>
    </form>
</div>
