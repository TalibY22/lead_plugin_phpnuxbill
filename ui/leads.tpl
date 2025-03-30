{include file="sections/header.tpl"}

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-users"></i> {$_L['Leads Management']}</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="widget style1 lazur-bg">
                            <div class="row">
                                <div class="col-xs-4">
                                    <i class="fa fa-users fa-5x"></i>
                                </div>
                                <div class="col-xs-8 text-right">
                                    <span> {$_L['Total Leads']} </span>
                                    <h2 class="font-bold">{$totalLeads}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget style1 navy-bg">
                            <div class="row">
                                <div class="col-xs-4">
                                    <i class="fa fa-comment-o fa-5x"></i>
                                </div>
                                <div class="col-xs-8 text-right">
                                    <span> {$_L['Active Leads']} </span>
                                    <h2 class="font-bold">{$totalActive}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="widget style1 blue-bg">
                            <div class="row">
                                <div class="col-xs-4">
                                    <i class="fa fa-trophy fa-5x"></i>
                                </div>
                                <div class="col-xs-8 text-right">
                                    <span> {$_L['Converted Leads']} </span>
                                    <h2 class="font-bold">{$totalConverted}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="{$_url}plugin/leads&action=add" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> {$_L['Add New Lead']}</a>
                        <a href="{$_url}plugin/leads&action=import" class="btn btn-success btn-block"><i class="fa fa-upload"></i> {$_L['Import Leads']}</a>
                        <a href="{$_url}plugin/leads&action=export{if $status}&status={$status}{/if}{if $source}&source={$source}{/if}{if $search}&search={$search}{/if}" class="btn btn-info btn-block"><i class="fa fa-download"></i> {$_L['Export Leads']}</a>
                    </div>
                </div>

                <div class="hr-line-dashed"></div>

                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" method="get" action="{$_url}plugin/leads">
                            <div class="form-group">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <span class="fa fa-search"></span>
                                        </div>
                                        <input type="text" name="search" class="form-control" placeholder="{$_L['Search by Name, Phone or Email']}" value="{$search}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control" id="status">
                                        <option value="">{$_L['All Statuses']}</option>
                                        {foreach explode(',', $_c['lead_statuses']) as $statusOption}
                                            <option value="{$statusOption}" {if $status eq $statusOption}selected{/if}>{$statusOption}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="source" class="form-control" id="source">
                                        <option value="">{$_L['All Sources']}</option>
                                        {foreach explode(',', $_c['lead_sources']) as $sourceOption}
                                            <option value="{$sourceOption}" {if $source eq $sourceOption}selected{/if}>{$sourceOption}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-block">{$_L['Filter']}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="hr-line-dashed"></div>

                {if isset($leads) && count($leads) > 0}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{$_L['Name']}</th>
                                <th>{$_L['Phone']}</th>
                                <th>{$_L['Email']}</th>
                                <th>{$_L['Source']}</th>
                                <th>{$_L['Status']}</th>
                                <th>{$_L['Created At']}</th>
                                <th>{$_L['Actions']}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $leads as $lead}
                                <tr>
                                    <td>{$lead@iteration}</td>
                                    <td>{$lead['name']}</td>
                                    <td>{$lead['phone']}</td>
                                    <td>{$lead['email']}</td>
                                    <td><span class="label label-info">{$lead['source']}</span></td>
                                    <td>
                                        {if $lead['status'] eq 'New'}
                                            <span class="label label-primary">{$lead['status']}</span>
                                        {elseif $lead['status'] eq 'Converted'}
                                            <span class="label label-success">{$lead['status']}</span>
                                        {elseif $lead['status'] eq 'Lost'}
                                            <span class="label label-danger">{$lead['status']}</span>
                                        {else}
                                            <span class="label label-default">{$lead['status']}</span>
                                        {/if}
                                    </td>
                                    <td>{$lead['created_at']}</td>
                                    <td>
                                        <a href="{$_url}plugin/leads&action=edit&id={$lead['id']}" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i> {$_L['Edit']}</a>
                                        {if $lead['status'] neq 'Converted'}
                                            <a href="{$_url}plugin/leads&action=convert&id={$lead['id']}" class="btn btn-xs btn-success"><i class="fa fa-exchange"></i> {$_L['Convert']}</a>
                                        {/if}
                                        <a href="{$_url}plugin/leads&action=delete&id={$lead['id']}" class="btn btn-xs btn-danger" onclick="return confirm('{$_L['Are you sure you want to delete this lead?']}');"><i class="fa fa-trash"></i> {$_L['Delete']}</a>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>

                {if $totalPages > 1}
                <ul class="pagination">
                    {if $currentPage > 1}
                        <li><a href="{$_url}plugin/leads?page={$currentPage-1}{if $status}&status={$status}{/if}{if $source}&source={$source}{/if}{if $search}&search={$search}{/if}">&laquo;</a></li>
                    {/if}
                    
                    {for $i=1 to $totalPages}
                        <li {if $i eq $currentPage}class="active"{/if}><a href="{$_url}plugin/leads?page={$i}{if $status}&status={$status}{/if}{if $source}&source={$source}{/if}{if $search}&search={$search}{/if}">{$i}</a></li>
                    {/for}
                    
                    {if $currentPage < $totalPages}
                        <li><a href="{$_url}plugin/leads?page={$currentPage+1}{if $status}&status={$status}{/if}{if $source}&source={$source}{/if}{if $search}&search={$search}{/if}">&raquo;</a></li>
                    {/if}
                </ul>
                {/if}

                {else}
                <div class="alert alert-info">
                    <strong>{$_L['No leads found']}</strong>
                </div>
                {/if}
            </div>
        </div>
    </div>
</div>

{include file="sections/footer.tpl"}