<h1 style="font-size: 18px;">{l s='Order number' mod='quickpay'} {$order} {l s='Complete' mod='quickpay'}</h1><br />
<p>{l s='Your order is' mod='quickpay'} {l s='is complete.' mod='quickpay'}
	<br /><br /><span class="bold">{l s='Your order has bin assigned order number:' mod='quickpay'} {$order}</span>
	<br /><br />{l s='For any questions or for further information, please contact our' mod='quickpay'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='quickpay'}</a>.

{$HOOK_ORDER_CONFIRMATION}

      <br /><br /><a href="{$base_dir_ssl}history.php">{l s='View order history' mod='quickpay'}</a><br /><br />
</p>
