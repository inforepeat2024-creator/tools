<?php

namespace RepeatToolkit\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;

class AbstractController extends Controller
{

    /**
     * Make standard response with some data
     *
     * @param object|array $data Data to be sent as JSON
     * @param int $code optional HTTP response code, default to 200
     * @return JsonResponse
     */
    protected function respondWithData(object|array $data, int $code = 200): JsonResponse
    {
        return Response::json([
            'success' => true,
            'message' => __i("Uspešna akcija"),
            'data' => $data
        ], $code);
    }

    /**
     * Make standard successful response ['success' => true, 'message' => $message]
     *
     * @param string $message Success message
     * @param int $code HTTP response code, default to 200
     * @return JsonResponse
     */
    protected function respondSuccess(string $message = 'Done!', int $code = 200): JsonResponse
    {
        if($message == 'Done!')
            $message = __i("Uspešna akcija");

        return Response::json([
            'success' => true,
            'message' => $message
        ], $code);
    }

    /**
     * Make standard response with error ['success' => false, 'message' => $message]
     *
     * @param string $message Error message
     * @param int $code HTTP response code, default to 500
     * @return JsonResponse
     */
    protected function respondWithError(string $message = 'Server error', int $code = 500): JsonResponse
    {


        return Response::json([
            'success' => false,
            'message' => $message,
            'error' => 1,
        ], $code);
    }


    public function redirectWithError($e, $route = null)
    {
        if(Str::contains(url()->current(), ['localhost']))
        {
            $message = $e->getMessage() . $e->getFile() . $e->getLine();
        }
        else
        {
            $message =__i("Došlo je do greške.");

            // $message = $e->getMessage();
        }

        //dd($message);

        $params = ['error' => 1, 'message' => $e->getMessage() ,'error_message' => $e->getMessage(), 'error_file' => $e->getFile(), 'error_line' => $e->getLine(), 'is_user_exception' => $e instanceof UserDefinedException];

        Log::error(json_encode($params));

        if($route != null)
            return redirect()->to($route)->withError($message)->withInput()->with($params);

        return redirect()->back()->withError($message)->withInput()->with($params);
    }

    public function redirectWithSuccess($message = "", $route = null)
    {

        if($route != null)
            return redirect()->to($route)->with(['message' => __i("Uspešna akcija"), 'success' => 1]);


        if($message != "")
        {
            return redirect()->back()->with(['message' => $message, 'success' => 1]);
        }

        return redirect()->back()->with(['message' => __i("Uspešna akcija"), 'success' => 1]);


    }

}