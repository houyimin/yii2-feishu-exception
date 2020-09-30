<?php
namespace houyimin\feishulog;

use yii\log\Target;
use yii\di\ServiceLocator;
use yii\log\LogRuntimeException;
use yii\base\InvalidConfigException;

/**
 * ```php
 * 'components' => [
 *     'log' => [
 *          'targets' => [
 *              [
 *                  'class' => 'houyimin\feishulog\ExceptionTarget',
 *                  'levels' => ['error', 'warning'],
 *                  'options' => [
 *                      'accessToken' => 'xxxxxxxxx'
 *                  ],
 *              ],
 *          ],
 *     ],
 * ],
 * ```
 *
 */
class ExceptionTarget extends Target
{
    /**
     * @var array
     */
    public $options = [];


    public function init()
    {
        parent::init();
        if (empty($this->options['accessToken'])) {
            throw new InvalidConfigException('The "accessToken" option must be set.');
        }
    }

    /**
     * Sends log messages to feishu.
     * @return mixed
     * @throws LogRuntimeException
     */
    public function export()
    {
        $messages = array_map([$this, 'formatMessage'], $this->messages);

        $response = $this->sendMsg($messages);
        if ($response['StatusCode']!== 0) {
            throw new LogRuntimeException('Unable to export log through feishu!');
        }else{
            return $response;
        }
    }

    /**
     * @param $message
     * @return mixed
     */
    public function sendMsg($message){
        $locator = new ServiceLocator;
        $locator->setComponents([
            'robot' => [
                'class' => 'houyimin\feishu\Robot',
                'accessToken' => $this->options['accessToken']
            ],
        ]);
        $robot = $locator->get('robot');
        $response = $robot->sendTextMsg($message);
        return json_decode($response,true);
    }
}
