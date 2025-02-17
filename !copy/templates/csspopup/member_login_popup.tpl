<div id="member_login_popup" class="default_popup login_popup popUpDiv" style="display:none; width: 300px;">
    <div style="float: right; overflow:auto; margin-bottom: 5px;">
        <a onClick="popupClose('member_login_popup');">
            <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
        </a>
    </div>
    <h3>Member's Login</h3>
    <form method="post" name="login_form" action="/login.html">
        <p>Username<br/><input type="text" name="username" id="username" style="width: 290px;" onFocus="this.className += 'focus';" onBlur="this.className='';"/></p>
        <p>Password<br/><input type="password" name="password" id="password" style="width: 290px;" onFocus="this.className += 'focus';" onBlur="this.className='';" /></p>
        <p><div style="float: right;"><a class="approve_button" id="login_sign_in" onclick="document.login_form.submit();">Sign In</a></div>
        <input name="remember" type="checkbox" value="1" />
        Remember Me!</p>
        <p><a href="/forgot.html">Forgot your username/password?</a></p>

    </form>
</div>
