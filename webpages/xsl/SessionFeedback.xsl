<?xml version="1.0" encoding="UTF-8" ?>
<!--
	Created by BC Holmes on 2021-12-16;
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template match="/">
    <div class="container">
        <div class="card mt-3">
            <div class="card-header">
                <h2>Session Feedback</h2>
            </div>
            <div class="card-body">
                <div id="load-spinner" class="text-center" style="display: none">
                  <div class="spinner-border m-5" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                </div>
                <p>Please specify your interest in the following proposed sessions.</p>
                <div id="session-list">
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="javascript/zambiaExtension.js" />
    <script type="text/javascript" src="javascript/zambiaExtensionFeedback.js" />
  </xsl:template>
</xsl:stylesheet>
