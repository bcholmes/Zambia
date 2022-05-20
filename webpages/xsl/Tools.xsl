<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">

        <div class="list-group mt-3 mb-2">
            <div class="list-group-item flex-column align-items-start">
                <div class="row">
                    <div class="col-md-10">
                        <h4>Table Tents</h4>
                        <div>Produce a printable version of the table tents for the various con sessions.</div>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="TableTentsConfig.php" class="btn btn-primary w-75">Select</a>
                    </div>
                </div>
            </div>
            <div class="list-group-item flex-column align-items-start">
                <div class="row">
                    <div class="col-md-10">
                        <h4>Schedule Enumeration Tool</h4>
                        <div>Assigns incremental numbers to all scheduled sessions.</div>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="SessionEnumerationTool.php" class="btn btn-primary w-75">Select</a>
                    </div>
                </div>
            </div>
            <div class="list-group-item flex-column align-items-start">
                <div class="row">
                    <div class="col-md-10">
                        <h4>Export WisSched JSON file</h4>
                        <div>Create a version of the JSON file that WisSched uses as its initial data.</div>
                    </div>
                    <div class="col-md-2 text-right">
                        <a href="mobile/MobileJsonExport.php" class="btn btn-primary w-75" target="_blank" rel="noreferrer">Select</a>
                    </div>
                </div>
            </div>
        </div>

    </xsl:template>
</xsl:stylesheet>