<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../vendor/autoload.php';

function sendSupplyRequestEmail($name, $department, $item, $product_id, $unit, $quantity)
{
    $department = strtoupper($department);
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'g.cabelin231@gmail.com';
        $mail->Password   = 'zqtz ztnt lwdo cvrq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('g.cabelin231@gmail.com', 'Supply Request Notice');
        $mail->addAddress('geraldsolo63@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Supply Request';

        $mail->Body = "
            <h3>Supply Request Submitted</h3>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Department:</strong> $department</p>
            <p><strong>Item:</strong> $item</p>
            <p><strong>Product ID:</strong> $product_id</p>
            <p><strong>Unit:</strong> $unit</p>
            <p><strong>Quantity:</strong> $quantity</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
