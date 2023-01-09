<?php

$authUrl = $this->getVar('authUrl');

/** @var \QuickBooksOnline\API\Data\IPPCompany $companyInfo */
$companyInfo = $this->getVar('companyInfo');

?>
<script>

    var url = '<?php echo $authUrl; ?>';

    var OAuthCode = function (url) {

        this.loginPopup = function (parameter) {
            this.loginPopupUri(parameter);
        };

        this.loginPopupUri = function (parameter) {

            // Launch Popup
            var parameters = "location=1,width=800,height=650";
            parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

            var win = window.open(url, 'connectPopup', parameters);
            var pollOAuth = window.setInterval(function () {
                try {

                    if (win.document.URL.indexOf("code") != -1) {
                        window.clearInterval(pollOAuth);
                        win.close();
                        location.reload();
                    }
                } catch (e) {
                    console.log(e)
                }
            }, 100);
        }
    };

    var apiCall = function () {

        this.refreshToken = function () {
            $.ajax({
                type: "POST",
                url: "/admin/qb/refresh",
            }).done(function (result) {
                result = JSON.parse(result);
                if (typeof result.result == 'undefined' || result.result != 'success') {
                    alert('There was an error refreshing the token');
                } else {
                     location.reload();
                }
            });
        }
    };

    var oauth = new OAuthCode(url);
    var apiCall = new apiCall();

</script>

<h1 class="page_header">QB Connect Page</h1>


<h4><a target="_balnk" href="https://developer.intuit.com/docs/00_quickbooks_online/2_build/10_authentication_and_authorization/10_oauth_2.0">OAuth2.0 Documentation</a></h4>

<?php if (empty($companyInfo)) { ?>

     <p>Quick Books is not connected.</p>

    <a class="imgLink mb-3" href="#" onclick="oauth.loginPopup()"><img src="/images/C2QB_green_btn_lg_default.png" width="178" /></a><br />

<?php } else { ?>

    <p>A connection to QuickBooks has been established. Click disconnect below to remove the connection.</p>

    <p><strong>Connected Company:</strong> <?=$companyInfo->CompanyName?></p>

    <button  type="button" class="btn btn-success mt-3" onclick="apiCall.refreshToken()">Refresh Token</button>
    <a href="/admin/qb/disconnect" class="btn btn-success mt-3" >Disconnect</a>

<?php } ?>

