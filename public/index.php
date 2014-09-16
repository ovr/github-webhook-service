<?php
/**
 * @author Patsura Dmitry http://github.com/ovr <talk@dmtry.me>
 */

$appDir = __DIR__ . '/../app/';

include_once $appDir . 'config.php';

if (isset($_POST['payload'])) {
    $requestInfo = json_decode($_POST['payload']);

    $config = new Config($appDir);
    $configuration = $config->toArray();

    if (isset($configuration[$requestInfo->repository->owner->name])) {
        $parameters = $configuration[$requestInfo->repository->owner->name];

        if (isset($parameters[$requestInfo->repository->name])) {
            $parameters = $parameters[$requestInfo->repository->name];

            if (isset($parameters['secret'])) {
                parse_str($_SERVER['HTTP_X_HUB_SIGNATURE'], $hubSignatureStr);

                if (isset($hubSignatureStr[1]) && $hubSignatureStr[1] == hash_hmac('sha1', file_get_contents('php://input'), $parameters['secret'])) {
                    $result = $parameters['callback']();
                } else {
                    echo json_encode(array('success' => false, 'message' => 'wrong secret key'));
                    exit(1);
                }
            } else {
                echo json_encode(array('success' => false, 'message' => 'Please setup secret key for project in configuration'));
                exit(1);
            }
        } else {
            echo json_encode(array('success' => false, 'message' => 'No configuration for project: ' . $requestInfo->hook->repository->name));
            exit(1);
        }

    } else {
        echo json_encode(array('success' => false, 'message' => 'No config for user: ' . $requestInfo->repository->owner->name));
        exit(1);
    }

    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false, 'message' => 'No Payload present'));
}

