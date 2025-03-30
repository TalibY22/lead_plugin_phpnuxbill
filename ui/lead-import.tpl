{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-upload"></i> {$_L['Import Leads']}</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <form action="{$_url}plugin/leads&action=import" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="csv_file">{$_L['CSV File']} *</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                                <p class="help-block">{$_L['Upload a CSV file formatted as described below.']}</p>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {$_L['Import']}</button>
                                <a href="{$_url}plugin/leads" class="btn btn-default"><i class="fa fa-times"></i> {$_L['Cancel']}</a>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <h3 class="panel-title">{$_L['CSV Format Instructions']}</h3>
                            </div>
                            <div class="panel-body">
                                <p>{$_L['The CSV file must be formatted as follows:']}</p>
                                <ol>
                                    <li>{$_L['First row should contain column headers: Name, Phone, Email, Address, Source, Status, Notes']}</li>
                                    <li>{$_L['Each subsequent row should contain data for one lead.']}</li>
                                    <li>{$_L['Name and Phone fields are required for each lead.']}</li>
                                    <li>{$_L['Values should be comma-separated.']}</li>
                                </ol>
                                <p>{$_L['Example:']}</p>
                                <pre>Name,Phone,Email,Address,Source,Status,Notes
John Doe,+25470123456,john@example.com,123 Main St,Website,New,Interested in fiber
Jane Smith,+25471654321,jane@example.com,456 Oak Ave,Referral,Active,Follow up next week</pre>
                                <p>
                                    <a href="{$_url}plugin/leads&action=export_template" class="btn btn-xs btn-info"><i class="fa fa-download"></i> {$_L['Download CSV Template']}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}