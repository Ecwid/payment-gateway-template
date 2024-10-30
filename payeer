<؟ php
متجر m_shop $ = '' ؛
$ m_orderid = '1' ؛
$ m_amount = number_format (100، 2، '.'، '') ؛
دولار m_curr = "دولار أمريكي" ؛
$ m_desc = base64_encode ("اختبار") ؛
$ m_key = "مفتاحك السري"؛

$ arHash = مجموعة (
	$ m_shop،
	$ m_orderid،
	m_amount دولار ،
	دولار m_curr ،
	$ m_desc
);

/*
$ arParams = مجموعة (
	"Success_url" => "http: /// new_success_url" ،
	// 'fail_url' => 'http: /// new_fail_url' ،
	// 'status_url' => 'http: /// new_status_url' ،
	'مرجع' => مجموعة (
		"var1" => "1" ،
		// 'var2' => '2' ،
		// 'var3' => '3' ،
		// 'var4' => '4' ،
		// 'var5' => '5' ،
	),
	// 'submerchant' => 'mail.com' ،
);

$ key = md5 (''. $ m_orderid) ؛

$ m_params =urlencode (base64_encode (openssl_encrypt (json_encode ($ arParams) ، 'AES-256-CBC' ، مفتاح $ ، OPENSSL_RAW_DATA))) ؛

$ arHash [] = m_params دولار ؛
*/

$ arHash [] = $ m_key؛

علامة $ = strtoupper (التجزئة ('sha256'، تنفجر من الداخل (':'، $ arHash))) ؛
؟>
<form method = "post" action = "https://payeer.com/merchant/">
<input type = "hidden" name = "m_shop" value = "<؟ = $ m_shop؟>">
<input type = "hidden" name = "m_orderid" value = "<؟ = $ m_orderid؟>">
<input type = "hidden" name = "m_amount" value = "<؟ = $ m_amount؟>">
<input type = "hidden" name = "m_curr" value = "<؟ = $ m_curr؟>">
<input type = "hidden" name = "m_desc" value = "<؟ = $ m_desc؟>">
<input type = "hidden" name = "m_sign" value = "<؟ = $ sign؟>">
<؟ php / *
<input type = "hidden" name = "form [ps]" value = "2609">
<input type = "hidden" name = "شكل [تيار [2609]]" قيمة = "دولار أمريكي">
* /؟>
<؟ php / *
<input type = "hidden" name = "m_params" value = "<؟ = $ m_params؟>">
<input type = "hidden" name = "m_cipher_method" value = "AES-256-CBC">
* /؟>
<input type = "submit" name = "m_process" value = "send" />
</form>
