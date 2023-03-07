<!DOCTYPE html>
<html>
<body>
    <p>Dear {{ $details['name'] }}, </p>

    <p>We're writing to thank you for your recent payment towards your loan with ID {{ $details['hid'] }} with our company. We appreciate your promptness in fulfilling your financial obligations and your commitment to maintaining your creditworthiness.</p>

    <p>As per the loan agreement, your next payment is due on {{ $details['nextPaymentDate'] }}. The amount due is {{ $details['remaining'] }}.</p>

    <p>Please ensure that the payment is received by the due date.<!-- to avoid late fees and penalties. --></p>

    <p>We understand that managing finances can be a challenging task, and we are here to help you in any way we can. If you have any questions or concerns about your loan or payment, please do not hesitate to contact us.</p>

    <p>Thank you again for your payment and for choosing our company for your lending needs. If you have any questions or concerns, please do not hesitate to contact us.</p>

    <p>Best regards,<br />
    <br />
    Aspire Loan Team
    </p>
</body>
</html>