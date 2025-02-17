<div id="member_login_popup" class="popUpDiv" style="display:none; width: 500px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a onClick="popupClose('member_login_popup');">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><a href="{pageValue(#mainurl#)}" title="{pageValue(#sitename#)}"><img src="/images/webcomictv-account.png" alt="{pageValue(#sitename#)}" width="85px" height="26px" border="0" align="absmiddle" title="{pageValue(#sitename#)}" /></a> Account Login</h2>
        <form action="/login.html" method="post" name="login_form" id="login_form">
            <table class="hidden">
                <tr><th>Username</th><td><input name="username" type="text" onfocus="this.select()" style="width: 98%"/></td></tr>
                <tr><th>Password</th><td><input name="password" type="password" onFocus="this.select()"  style="width: 98%"/></td></tr>
                <tr>
                    <th colspan="2">
                        <div style="float: right">
                            <a class="approve_button" onClick="document.login_form.submit();">Log In To {pageValue(#sitename#)}</a>
                        </div>
                        <label><input name="remember" type="checkbox" value="1" />
                        Remember Me!</label>
                    </th>
                </tr>
            </table>

            <div style="float: right;"><a href="/forgot.html" rel="nofollow">Forgot Password?</a></div>
            <div>Not a member? <a href="register.html" rel="nofollow">Register Now!</a></div>
        </form>

    </div>
</div>