<div class="container">

    <h1><a href="/contact.html" title="Contact {pageValue(#sitename#)}">Contact {pageValue(#sitename#)}</a></h1>
    <div class="row">
        <div class="col-xs-12">
            {if:status_message}
                {status_message:h}
                {end:}
            <h2>Compose Message</h2><p>Thank you for your interest in {pageValue(#sitename#)}. We are more than pleased to address your questions or comments and are always welcome to hear any suggestions you may have. If you would like additional information about our services please fill out the form below and we will get back to you as soon as possible.</p>

            <form method="post" name="contact_form" id="contact_form">
                <div class="form-group">
                    <label for="email_address">Email address</label>
                    <input type="text" class="form-control" id="email_address" name="email_address" placeholder="Enter Email Address">
                </div>

                <div class="form-group">
                    <label for="email_message">Question/Comment:</label>
                    <textarea name="email_message" cols="60" rows="8" id="email_message" class="form-control" placeholder="Question/Comment" ></textarea>
                </div>
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="6Lf5xgUTAAAAAH3NffiU9Cf8DSMlY6OUZKjjUqo_"></div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block" >Send Message</a>
                </div>
            </form>
            <div class="small"><em>*All Fields are Required information.</em></div>
        </div>

    </div>
</div>




