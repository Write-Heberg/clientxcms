<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin\Core;

use App\Http\Controllers\Admin\AbstractCrudController;
use App\Models\Account\EmailMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailController extends AbstractCrudController
{
    protected string $viewPath = 'admin.core.emails';
    protected string $routePath = 'admin.emails';
    protected string $model = EmailMessage::class;
    protected string $searchField = 'subject';
    protected string $translatePrefix = 'admin.emails';

    public function show(EmailMessage $email)
    {
        staff_aborts_permission('admin.show_email_messages');
        return new Response($email->content, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->query('q');
        return EmailMessage::where('subject', 'LIKE', "%{$search}%")->paginate($this->perPage);
    }

    public function destroy(EmailMessage $email)
    {
        staff_aborts_permission('admin.show_emails');
        $email->delete();
        return $this->deleteRedirect($email);
    }
}
