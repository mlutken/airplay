<form action="https://secure.quickpay.dk/form" method="post" id="quickpay{$type}">
  <input type="hidden" name="protocol" value="{$protocol}" />
  <input type="hidden" name="msgtype" value="{$msgtype}" />
  <input type="hidden" name="merchant" value="{$merchant}" />
  <input type="hidden" name="language" value="{$language}" />
  <input type="hidden" name="ordernumber" value="{$ordernumber}" />
  <input type="hidden" name="amount" value="{$total}" />
  <input type="hidden" name="currency" value="{$currency}" />
  <input type="hidden" name="continueurl" value="{$okpage}" />
  <input type="hidden" name="cancelurl" value="{$errorpage}" />
  <input type="hidden" name="callbackurl" value="{$resultpage}" />
  <input type="hidden" name="autocapture" value="{$autocapture}" />
  <input type="hidden" name="cardtypelock" value="{$cardtypelock}" />
  <input type="hidden" name="description" value="{$description}" />
  <input type="hidden" name="splitpayment" value="{$splitpayment}" />
  <input type="hidden" name="testmode" value="{$testmode}" />
  <input type="hidden" name="autofee" value="{$autofee}" />
  <input type="hidden" name="md5check" value="{$md5check}" />
{if $type == "viabill"}
  <input type="hidden" name="CUSTOM_email" value="{$customer->email}" />
  <input type="hidden" name="CUSTOM_invoice_company" value="{$invoiceAddress->company}" />
  <input type="hidden" name="CUSTOM_invoice_firstname" value="{$invoiceAddress->firstname}" />
  <input type="hidden" name="CUSTOM_invoice_lastname" value="{$invoiceAddress->lastname}" />
  <input type="hidden" name="CUSTOM_invoice_invoiceAddress" value="{$invoiceAddress->address1}" />
  <input type="hidden" name="CUSTOM_invoice_address2" value="{$invoiceAddress->address2}" />
  <input type="hidden" name="CUSTOM_invoice_postcode" value="{$invoiceAddress->postcode}" />
  <input type="hidden" name="CUSTOM_invoice_city" value="{$invoiceAddress->city}" />
  <input type="hidden" name="CUSTOM_invoice_phone" value="{$invoiceAddress->phone}" />
  <input type="hidden" name="CUSTOM_invoice_phone_mobile" value="{$invoiceAddress->phone_mobile}" />
  <input type="hidden" name="CUSTOM_invoice_vat_number" value="{$invoiceAddress->vat_number}" />
  <input type="hidden" name="CUSTOM_delivery_company" value="{$deliveryAddress->company}" />
  <input type="hidden" name="CUSTOM_delivery_firstname" value="{$deliveryAddress->firstname}" />
  <input type="hidden" name="CUSTOM_delivery_lastname" value="{$deliveryAddress->lastname}" />
  <input type="hidden" name="CUSTOM_delivery_invoiceAddress" value="{$deliveryAddress->address1}" />
  <input type="hidden" name="CUSTOM_delivery_address2" value="{$deliveryAddress->address2}" />
  <input type="hidden" name="CUSTOM_delivery_postcode" value="{$deliveryAddress->postcode}" />
  <input type="hidden" name="CUSTOM_delivery_city" value="{$deliveryAddress->city}" />
  <input type="hidden" name="CUSTOM_delivery_phone" value="{$deliveryAddress->phone}" />
  <input type="hidden" name="CUSTOM_delivery_phone_mobile" value="{$deliveryAddress->phone_mobile}" />
  <input type="hidden" name="CUSTOM_delivery_vat_number" value="{$deliveryAddress->vat_number}" />
{/if}
{if $type == "paii"}
  <input type="hidden" name="CUSTOM_reference_title" value="{$shopName}" />
  <input type="hidden" name="CUSTOM_category" value="SC21" />
  <input type="hidden" name="CUSTOM_product_id" value="P03" />
  <input type="hidden" name="CUSTOM_vat_amount" value="{$taxTotal}" />
{/if}

</form>
<p class="payment_module">
  <a style="height:auto" href="javascript:$('#quickpay{$type}').submit()">
    {foreach from=$imgs item=img}
      {if $imgs|@count gt 2}
	<img src="{$module_dir}imgf/{$img}.gif" style="margin:0px 2px 2px 0px" alt="{l s='Pay with credit cards ' mod='quickpay'}" />
      {else}
	<img src="{$module_dir}img/{$img}.png" style="margin:0px 0px 2px 0px" alt="{l s='Pay with credit cards ' mod='quickpay'}" />
      {/if}
    {/foreach}
    &nbsp;
    {$text}
    {if $fee}
    <i>
    {$fee}
    </i>
    {/if}
  </a>
</p>
