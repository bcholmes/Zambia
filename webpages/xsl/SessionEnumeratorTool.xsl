<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">

        <div class="card mt-3">
            <div class="card-header">
                <h2>Session Enumeration</h2>
            </div>
            <div class="card-body">
                <p>This tool assigns simple numbers to each of the sessions on the current schedule. The numbers should be assigned
                based on start time, and room. The intention is that if you look at the grid view of the session, the numbers appear
                to start at '1' and increment as you read left-to-right, top-to-bottom.</p>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary action-button">Proceed</button>
            </div>
        </div>
        <script type="text/javascript" src="js/planzExtension.js" />
        <script type="text/javascript" src="js/planzExtensionSessionEnumeration.js" />

    </xsl:template>
</xsl:stylesheet>