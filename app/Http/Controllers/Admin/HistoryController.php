<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Core\LogsReaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        staff_aborts_permission('admin.show_logs');
        $reader = new LogsReaderService();
        $folderFiles = [];
        if ($request->input('f')) {
            $reader->setFolder(Crypt::decrypt($request->input('f')));
            $folderFiles = $reader->getFolderFiles(true);
        }

        if ($request->input('l')) {
            $reader->setFile(Crypt::decrypt($request->input('l')));
        }
        $data = [
            'folders' => $reader->getFolders(),
            'current_folder' => $reader->getFolderName(),
            'folder_files' => $folderFiles,
            'files' => $reader->getFiles(true),
            'current_file' => $reader->getFileName(),
            'standardFormat' => true,
            'structure' => $reader->foldersAndFiles(),
            'storage_path' => $reader->getStoragePath(),
            'content' => $reader->get(),
        ];

        if ($request->wantsJson())
            return $data;
        return view('admin.dashboard.history.index', $data);
    }

    public function download(Request $request)
    {
        staff_aborts_permission('admin.show_logs');
        $file = Crypt::decrypt($request->input('dl'));
        return response()->download((new LogsReaderService())->pathToLogFile($file));
    }

    public function clear(Request $request)
    {
        staff_aborts_permission('admin.show_logs');
        $file = Crypt::decrypt($request->input('clean'));
        \File::put((new LogsReaderService())->pathToLogFile($file), '');
        return back()->with('success', 'File has been cleared');
    }

    public function delete(Request $request)
    {
        staff_aborts_permission('admin.show_logs');
        $file = $request->input('del');
        \File::delete((new LogsReaderService())->pathToLogFile($file));
        return back()->with('success', 'File has been deleted');
    }

    public function deleteAll(Request $request)
    {
        staff_aborts_permission('admin.show_logs');
        $reader = new LogsReaderService();
        if ($request->input('f')) {
            $reader->setFolder(Crypt::decrypt($request->input('f')));
        }
        $files = $reader->getFolderName() ? $reader->getFolderFiles(true) : $reader->getFiles(true);
        foreach ($files as $file) {
            \File::delete($file);
        }
        return back()->with('success', 'All files have been deleted');
    }

}
