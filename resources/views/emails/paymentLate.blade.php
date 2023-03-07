<!DOCTYPE html>
<html>
<body>
    <p>Dear {{ $details['name'] }}, </p>

    <p>I hope this email finds you well. I am writing to remind you that your payment for your loan request {{ $details['hid'] }} is now {{ $details['lateInDays'] }} day(s) past due.</p>

    <p>We understand that unforeseen circumstances can arise, and we want to work with you to ensure that you can fulfill your financial obligations. We kindly request that you make the payment as soon as possible. <!-- to avoid any further charges or penalties. --> The amount due is {{ $details['remaining'] }}.</p>

    <p>Please note that we are here to help you manage your loan, and we can work with you to find a payment plan that fits your budget. If you are experiencing any financial difficulties, please do not hesitate to contact us, and we can discuss your options.</p>

    <p>We value your business and your commitment to your loan, and we look forward to resolving this matter as soon as possible.</p>

    <p>Thank you for your attention to this matter.</p>

    <p>Best regards,<br />
    <br />
    Aspire Loan Team
    </p>
</body>
</html>