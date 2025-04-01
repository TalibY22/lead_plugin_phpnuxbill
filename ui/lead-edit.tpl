{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> {$_L['Edit Lead']}</h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="{$_url}plugin/leads&action=edit&id={$lead.id}">
                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Full Name']} *</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" value="{$lead.name}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Phone']} *</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="phone" value="{$lead.phone}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Email']}</label>
                        <div class="col-md-6">
                            <input type="email" class="form-control" name="email" value="{$lead.email}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Address']}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="address" rows="3">{$lead.address}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Source']}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="source">
                                {foreach explode(',', $_c['lead_sources']) as $source}
                                    <option value="{$source}" {if $lead.source eq $source}selected{/if}>{$source}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Status']}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="status">
                                {foreach explode(',', $_c['lead_statuses']) as $status}
                                    <option value="{$status}" {if $lead.status eq $status}selected{/if}>{$status}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Assigned To']}</label>
                        <div class="col-md-6">
                            <select class="form-control" name="assigned_to">
                                <option value="0" {if $lead.assigned_to eq '0'}selected{/if}>{$_L['None']}</option>
                                {foreach $staffMembers as $staff}
                                    <option value="{$staff.id}" {if $lead.assigned_to eq $staff.id}selected{/if}>{$staff.fullname}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-2 control-label">{$_L['Notes']}</label>
                        <div class="col-md-6">
                            <textarea class="form-control" name="notes" rows="5">{$lead.notes}</textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-6">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {$_L['Save']}</button>
                            <a href="{$_url}plugin/leads" class="btn btn-default"><i class="fa fa-times"></i> {$_L['Cancel']}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}