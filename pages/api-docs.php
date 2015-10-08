<?php
	/*
		Kite URL Shortener
		Version 1.0.0
		iAlex (http://codecanyon.net/iAlex)
		api-docs.php
	*/

    $kt = new Kite();
	global $Site;

    if($kt->Loggedin()) {
        $UserInfo = $kt->GetUser($_SESSION['KiteUserID']);
        echo '<p style="font-size:12px;">API Key: ' . $UserInfo[0]['apiKey'] . '<span class="pull-right">API Usage: ' . number_format($kt->GetApiUsage($_SESSION['KiteUserID'])) . '/' . number_format($Site[0]['api_usage']) . '</span></p><hr>';
    }
?>
<h4>API Documentation</h4>
<p>Kite's API allows developers to interact with our link shrinking capabilities. Using our API you're also able to view the amount of visits to each URL.
	At this time our API only supports JSON responses.
</p>
<br />

<h4>Authentication</h4>
<p>All requests currently require an API key parameter (field name should be apiKey). This will be shown above once you are logged in.</p>
<br />

<h4>Status Codes</h4>
<p>Every API call will return a "status" field, below shows the meaning to each one.</p>
<span class="indentx2">0 - Success<br /></span>
<span class="indentx2">1 - Invalid URL (invalid_url)<br /></span>
<span class="indentx2">2 - Invalid API key (invalid_apikey)<br /></span>
<span class="indentx2">3 - Missing parameters (missing_params)<br /></span>
<span class="indentx2">4 - Invalid short URL (invalid_short)<br /></span>
<span class="indentx2">5 - Reached the API limit per 24hrs (reached_api_limit)<br /></span>
<br />

<h4>API Methods</h4>
<p><b>Shorten a URL</b><br />
	<u class="indent">Method</u>:<br />
	<span class="indentx2">GET<br /></span>
	
	<u class="indent">Request Params</u>:<br />
	<span class="indentx2">apiKey<br /></span>
	<span class="indentx2">longUrl<br /></span>

	<u class="indent">Response</u>:<br />
	<span class="indentx2">Returns short url data on success<br /></span>
	
	<u class="indent">Example Call</u>:<br />
	<span class="indentx2">/?apiKey=1b2b374b212&amp;longUrl=http://google.co.uk<br /></span>
	
	<u class="indent">Example Response</u>:<br />
	<span class="indentx2">{"status":"0","response":"http:\/\/kt.tf\/7j0Tn0c"}</span>
</p>

<p><b>Expand a URL</b><br />
	<u class="indent">Method</u>:<br />
	<span class="indentx2">GET<br /></span>
	
	<u class="indent">Request Params</u>:<br />
	<span class="indentx2">apiKey<br /></span>
	<span class="indentx2">shortUrl<br /></span>
	
	<u class="indent">Response</u>:<br />
	<span class="indentx2">Returns original url data on success<br /></span>
	
	<u class="indent">Example Call</u>:<br />
	<span class="indentx2">/?apiKey=1b2b374b212&amp;shortUrl=Glq49qB&amp;expand<br /></span>
	
	<u class="indent">Example Response</u>:<br />
	<span class="indentx2">{"status":"0","response":"http:\/\/google.co.uk"}</span>
</p>

<p><b>URL Views</b><br />
	<u class="indent">Method</u>:<br />
	<span class="indentx2">GET<br /></span>
	
	<u class="indent">Request Params</u>:<br />
	<span class="indentx2">apiKey<br /></span>
	<span class="indentx2">shortUrl<br /></span>
	
	<u class="indent">Response</u>:<br />
	<span class="indentx2">Returns the amount of views on the URL<br /></span>
	
	<u class="indent">Example Call</u>:<br />
	<span class="indentx2">/?apiKey=1b2b374b212&amp;shortUrl=Glq49qB&amp;views<br /></span>
	
	<u class="indent">Example Response</u>:<br />
	<span class="indentx2">{"status":"0","response":"10"}</span>
</p>