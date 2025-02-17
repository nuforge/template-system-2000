
<div id="upload_site_image_popup" class="popUpDiv" style="display:none; width:400px;">
    <div class="round_dark">
        <div style="float: right; margin-bottom: 5px;">
            <a href="" onClick="popup('upload_site_image_popup'); return false;">
                <img src="/images/icons/cancel.png" title="cancel" width="16px" height="16px" alt="X" border="0"/>
            </a>
        </div>
        <h2><img src="/images/icons/photo_add.png" width="16" height="16" alt="Upload New Image" border="0" align="absmiddle"/> Upload New Image</h2>
        <p>Images must be 150x80 transparent 24bit pngs. Very, specific, I know, but damn do they look good.</p>
        <p>Uploading an icon will undo any unsaved changes you have made to the site. Please submit that form first.</p>
        <form method="post"  name="image_form" enctype="multipart/form-data"  >
            <input type="file" name="site_icon" id="site_icon" size="40"/>

            <a class="approve_button" onclick="document.image_form.submit();">Upload Icon</a>
            <a class="reject_button" onclick="popupClose('upload_site_image_popup'); return false;">Cancel</a>
            <input type="hidden" name="action" id="action" value="upload_site_image" flexy:ignore />
        </form>
    </div>
</div>