<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\DTO\Admin;

class MassActionDTO
{
    public string $action;
    public array $ids = [];
    public ?string $question = null;
    public ?string $response = null;
    public $callback;
    public string $translate;

    public function __construct(string $action, string $translate, callable $callback, ?string $question = null)
    {
        $this->action = $action;
        $this->callback = $callback;
        $this->question = $question;
        $this->translate = $translate;
    }

    public function execute(string $model)
    {
        $success = [];
        $errors = [];
        foreach ($this->ids as $id) {
            $model = $model::find($id);
            if ($model == null) {
                continue;
            }
            try {
                call_user_func_array($this->callback, [$model, $this->response]);
                $success[$model->id] = 'Success';
            } catch (\Exception $e) {
                $errors[$model->Id] = $e->getMessage();
            }
        }
        return [$success, $errors];
    }

    public function setResponse(array $ids, ?string $response = null): void
    {
        $this->ids = $ids;
        $this->response = $response;
    }
}
