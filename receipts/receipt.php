<div id="header" style="width: 680px; padding: 0px; margin: 0 auto; text-align: left;">
<h1 style="font-size: 30px; margin-bottom: 0;">Payment Receipt</h1>
<h2 style="margin-top: 0; color: #999; font-weight: normal;">for your payment to {$blog_name}</h2>
</div>
<div id="body" style="width: 600px; background: white; padding: 40px; margin: 0 auto; text-align: left;">
<table class="otslr-receipt-header" style="width: 100%;">
    <tr>
        <th>
            <img src="https://movenhome.com/wp-content/uploads/2025/04/1PNG-1.png">
        </th>
    </tr>
</table>

<table class="otslr-receipt-header" style="width: 100%;">
    <tr>
        <td>
            <p>If you scheduled your booking through the website or over the phone with one of our customer service representatives, your job is already confirmed, and a worker will arrive at the scheduled date and time. If you booked your reservation online a customer service representative may call you if we need any additional information about your job. Feel free to call us anytime between 8 AM and 8 PM, seven days a week at (888) 917-4412.
<p>Consent: I affirm that I've reviewed and agreed to the <a href="https://movenhome.com/terms-and-conditions/">Terms and Conditions</a> and the Privacy Policy.</p>
</td>
    </tr>
</table>

<table class="transaction">
<tbody>
    <tr>
        <th colspan="2"><strong>Receipt</strong></th>
    </tr>
    <tr>
        <th colspan="2"><strong>Transaction Details</strong></th>
    </tr>
    <tr>
        <td><strong>Entry id:</strong></td>
        <td>{$mepr_entry_id}</td>
    </tr>
    <tr>
        <td style="text-align: left;">Date:</td>
        <td>{$trans_date}</td>
    </tr>
</tbody>
</table>
<table class="transaction">
    <tbody>
    <tr>
        <th colspan="2"><strong>Entry Details</strong></th>
    </tr>
    <tr>
    <tr>
        <td style="text-align: left;">Amount Paid:</td>
        <td>{$payment_amount}</td>
    </tr>
    <tr>
        <td style="text-align: left;">Amount Due:</td>
        <td>{$mepr_amount_due}</td>
    </tr>
</tbody>
</table>
</div>