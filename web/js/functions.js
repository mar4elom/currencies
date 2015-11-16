function getTotal(obj)
{
	var currency, currency_rate, total;
	if(obj.id == 'sum_id')
	{
		currency = $('#currency_id option:selected').text();
		currency_rate = currency.split(' ');
		total = obj.value * currency_rate[1];
		$('#total_id').val(total.toPrecision(6));
	}

	if(obj.id == 'currency_id')
	{
		currency = $('#'+ obj.id +' option:selected').text();
		currency_rate = currency.split(' ');
		total = currency_rate[1] * $('#sum_id').val();
		$('#total_id').val(total.toPrecision(6));
		
		$('#currency_abbr').text($('#currency_id').val());
	}
}