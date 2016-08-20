{if $showcards}
<center>
{foreach from=$orderingList item=varName}
  <img src="{$module_dir}imgf/{$varName}.gif" alt="{l s='Pay with credit cards ' mod='quickpay'}" />
{/foreach}
</center><br />
{/if}
{if $viabilloverlay}
<!-- ViaBill overlay code -->
{$overlaycode}
<!-- /ViaBill overlay code -->
{/if}
