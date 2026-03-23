<?php

namespace Mcrm\Models\Api\AmoCRM;

use Mcrm\Models\Api\AmoCRM\Models\ModelInterface;
use Mcrm\Models\Api\AmoCRM\Request\CurlHandle;
use Mcrm\Models\Api\AmoCRM\Request\ParamsBag;
use Mcrm\Models\Api\AmoCRM\Helpers\Fields;
use Mcrm\Models\Api\AmoCRM\Helpers\Format;

/**
 * Class Client
 *
 * Основной класс для получения доступа к моделям amoCRM API
 *
 * @package AmoCRM
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/amocrm-php
 * @property \Mcrm\Models\Api\AmoCRM\Models\Account $account
 * @property \Mcrm\Models\Api\AmoCRM\Models\Call $call
 * @property \Mcrm\Models\Api\AmoCRM\Models\Catalog $catalog
 * @property \Mcrm\Models\Api\AmoCRM\Models\CatalogElement $catalog_element
 * @property \Mcrm\Models\Api\AmoCRM\Models\Company $company
 * @property \Mcrm\Models\Api\AmoCRM\Models\Contact $contact
 * @property \Mcrm\Models\Api\AmoCRM\Models\Customer $customer
 * @property \Mcrm\Models\Api\AmoCRM\Models\CustomersPeriods $customers_periods
 * @property \Mcrm\Models\Api\AmoCRM\Models\CustomField $custom_field
 * @property \Mcrm\Models\Api\AmoCRM\Models\Lead $lead
 * @property \Mcrm\Models\Api\AmoCRM\Models\Links $links
 * @property \Mcrm\Models\Api\AmoCRM\Models\Note $note
 * @property \Mcrm\Models\Api\AmoCRM\Models\Pipelines $pipelines
 * @property \Mcrm\Models\Api\AmoCRM\Models\Task $task
 * @property \Mcrm\Models\Api\AmoCRM\Models\Transaction $transaction
 * @property \Mcrm\Models\Api\AmoCRM\Models\Unsorted $unsorted
 * @property \Mcrm\Models\Api\AmoCRM\Models\Webhooks $webhooks
 * @property \Mcrm\Models\Api\AmoCRM\Models\Widgets $widgets
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Client
{
    /**
     * @var Fields|null Экземпляр Fields для хранения номеров полей
     */
    public $fields = null;

    /**
     * @var ParamsBag|null Экземпляр ParamsBag для хранения аргументов
     */
    public $parameters = null;

    /**
     * @var CurlHandle Экземпляр CurlHandle для повторного использования
     */
    private $curlHandle;

    /**
     * Client constructor
     *
     * @param string $domain Поддомен или домен amoCRM
     * @param string $login Логин amoCRM
     * @param string $apikey Ключ пользователя amoCRM
     * @param string|null $proxy Прокси сервер для отправки запроса
     */
    public function __construct($domain, $login, $apikey, $proxy = null)
    {
        // Разернуть поддомен в полный домен
        if (strpos($domain, '.') === false) {
            $domain = sprintf('%s.amocrm.ru', $domain);
        }

        $this->parameters = new ParamsBag();
        $this->parameters->addAuth('domain', $domain);
        $this->parameters->addAuth('login', $login);
        $this->parameters->addAuth('apikey', $apikey);

        if ($proxy !== null) {
            $this->parameters->addProxy($proxy);
        }

        $this->fields = new Fields();

        $this->curlHandle = new CurlHandle();
    }

    /**
     * Возвращает экземпляр модели для работы с amoCRM API
     *
     * @param string $name Название модели
     * @return ModelInterface
     * @throws ModelException
     */
    public function __get($name)
    {
        $classname = '\\Mcrm\Models\Api\AmoCRM\\Models\\' . Format::camelCase($name);

        if (!class_exists($classname)) {
            throw new ModelException('Model not exists: ' . $name);
        }

        // Чистим GET и POST от предыдущих вызовов
        $this->parameters->clearGet()->clearPost();

        return new $classname($this->parameters, $this->curlHandle);
    }
}
