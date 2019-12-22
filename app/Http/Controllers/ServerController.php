<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\ServerLog;

class ServerController extends Controller {
    public function getTrafficLog (Request $request) {
    	$type = $request->input('type') ? $request->input('type') : 0;
        $current = $request->input('current') ? $request->input('current') : 1;
        $pageSize = $request->input('pageSize') >= 10 ? $request->input('pageSize') : 10;
        $serverLogModel = ServerLog::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC');
    	switch ($type) {
    		case 0: $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')));
    			break;
    		case 1: $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-d')) - 604800);
    			break;
    		case 2: $serverLogModel->where('created_at', '>=', strtotime(date('Y-m-1')));
    	}
    	$sum = [
    		'u' => $serverLogModel->sum('u'),
    		'd' => $serverLogModel->sum('d')
    	];
        $total = $serverLogModel->count();
        $res = $serverLogModel->forPage($current, $pageSize)
            ->get();
        return response([
            'data' => $res,
            'total' => $total,
            'sum' => $sum
        ]);
    }
}