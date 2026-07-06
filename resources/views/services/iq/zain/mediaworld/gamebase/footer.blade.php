<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['service_name'] }} - Terms</title>
</head>
<body>
    <footer style="font-size:12px;">
        <p>Service: {{ $config['service_name'] }} | Operator: {{ $config['operator_display'] }}</p>
        <p>Price: {{ $config['price'] }} {{ $config['currency'] }}, billed {{ strtolower($config['billing_freq']) }}.</p>
        <p>
            To unsubscribe, send "{{ $config['unsub_keyword'] }}"
            to {{ $config['unsub_shortcode'] }}.
        </p>
    </footer>
</body>
</html>