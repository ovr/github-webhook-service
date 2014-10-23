<?php
/**
 * @author Patsura Dmitry http://github.com/ovr <talk@dmtry.me>
 */

$appDir = __DIR__ . '/../app/';

include_once $appDir . 'config.php';

function requestFailed($message, $code = 500) {
    echo json_encode(array('success' => false, 'message' => $message));
    exit(1);
}

if (isset($_POST['payload'])) {
    $requestInfo = json_decode($_POST['payload']);

    $config = new Config($appDir);
    $configuration = $config->toArray();

    if (isset($configuration[$requestInfo->repository->owner->name])) {
        $parameters = $configuration[$requestInfo->repository->owner->name];

        if (isset($parameters[$requestInfo->repository->name])) {
            $parameters = $parameters[$requestInfo->repository->name];

            if (isset($parameters['secret'])) {
                parse_str($_SERVER['HTTP_X_HUB_SIGNATURE'], $hubSignature);

                if (isset($hubSignature['sha1']) && isset($hubSignature['sha1']) == hash_hmac('sha1', file_get_contents('php://input'), $parameters['secret'])) {
                    $result = $parameters['callback']();
                } else {
                    requestFailed('wrong secret key');
                }
            } else {
                requestFailed('Please setup secret key for project in configuration');
            }
        } else {
            requestFailed('No configuration for project: ' . $requestInfo->hook->repository->name);
        }

    } else {
        requestFailed('No config for user: ' . $requestInfo->repository->owner->name);
    }

    echo json_encode(array('success' => true));
} else {
    requestFailed('No Payload present');
}

