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

    if (isset($configuration[$requestInfo->repository->owner->login])) {
        $parameters = $configuration[$requestInfo->repository->owner->login];

        if (isset($parameters[$requestInfo->repository->name])) {
            $parameters = $parameters[$requestInfo->repository->name];

            if (isset($parameters['secret'])) {
                if ($parameters['secret'] == $requestInfo->hook->config->secret) {
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
        echo json_encode(array('success' => false, 'message' => 'No config for user: ' . $requestInfo->repository->owner->login));
        exit(1);
    }

    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false, 'message' => 'No Payload present'));
}

