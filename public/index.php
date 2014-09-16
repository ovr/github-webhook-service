<?php
/**
 * @author Patsura Dmitry http://github.com/ovr <talk@dmtry.me>
 */

$appDir = __DIR__ . '/../app/';

include_once $appDir . 'config.php';

if (isset($_POST['Payload'])) {
    $requestInfo = json_decode($_POST['Payload']);

    $config = new Config($appDir);
    $configuration = $config->toArray();

    if (isset($configuration[$requestInfo->hook->repository->owner->login])) {
        $parameters = $configuration[$requestInfo->hook->repository->owner->login];
        if (isset($parameters['secret'])) {
            if ($parameters['secret'] == $requestInfo->hook->config->secret) {
                $result = $parameters['callback']();
            } else {
                echo json_encode(array('success' => false));
                exit(1);
            }
        }
    }

    echo json_encode(array('success' => true));
} else {
    echo json_encode(array('success' => false));
}
