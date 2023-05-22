<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GrupoController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use ApiResponser;

    /**
     * Authenticated User Instance.
     *
     * @var User
     */
    public User | null $user;
    public function __construct(Request $request)
    {
        $this->middleware('auth:api');
        $this->user = Auth::guard()->user();
    }

    /**
     * @OA\GET(
     *     path="/api/grupos",
     *     tags={"Grupos"},
     *     summary="Obter a lista de grupos cadastrados",
     *     description="Obter a lista de grupos cadastrados",
     *     operationId="indexGrupos",
     *     security={{"bearer":{}}},
     *     @OA\Response(response=200,description="OK"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function index()
    {
        try {
            $data = Grupo::all();
            return $this->success($data, 'Lista de grupos gerada com sucesso!');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\POST(
     *     path="/api/grupos",
     *     tags={"Grupos"},
     *     summary="Cadastrar novo grupo",
     *     description="Cadastrar novo grupo",
     *     operationId="storeGrupo",
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="descricao", type="string", example="descrição"),
     *              @OA\Property(property="status", type="string", example="1")
     *          ),
     *      ),
     *      security={{"bearer":{}}},
     *      @OA\Response(response=200, description="OK" ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function store(Request $request)
    {
        try {
            try{
                $attr = $request->validate([
                    'descricao' => 'required|string|max:255|unique:sistsic_grupos,descricao',
                    'status' => 'required|integer',
                ]);
            }catch (ValidationException $e) {
                $errors = $e->errors();
                return response()->json(['errors' => $errors], 422);
            }
            
            $data = [
                'descricao' => $attr['descricao'],
                'status' => $attr['status'],
            ];
            $grupo = Grupo::create($data);
            return $this->success($grupo, 'Grupo cadastrado com sucesso!');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/grupos/{id}",
     *     tags={"Grupos"},
     *     summary="Exibir detalhes de um grupo específico.",
     *     description="Exibir detalhes de um grupo específico.",
     *     operationId="showGrupos",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(name="id", description="id, eg; 1", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $data = Grupo::find($id);
            if (is_null($data)) {
                return $this->error('Group Not Found', Response::HTTP_NOT_FOUND, null);
            }

            return $this->success($data, 'Group Details Fetch Successfully !');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/grupos/{id}",
     *     tags={"Grupos"},
     *     summary="Editar grupo",
     *     description="Editar grupo",
     *     @OA\Parameter(name="id", description="id, eg; 1", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="descricao", type="string", example="nome"),
     *              @OA\Property(property="status", type="string", example="1"),
     *          ),
     *      ),
     *     operationId="updateGrupo",
     *     security={{"bearer":{}}},
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            try{
                $attr = $request->validate([
                    'descricao' => 'required|string|max:255|unique:sistsic_grupos,descricao',
                    'status' => 'required|integer',
                ]);
            }catch (ValidationException $e) {
                $errors = $e->errors();
                return response()->json(['errors' => $errors], 422);
            }

            $data = [
                'descricao' => $attr['descricao'],
                'status' => $attr['status'],
            ];

            $grupo = Grupo::find($id);

            if ($grupo){
                $grupo->update($data);
                return $this->success($data, 'Group Updated Successfully !');
            }else{
                return $this->error('Group Not Found', Response::HTTP_NOT_FOUND, null);
            }

            
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/grupos/{id}",
     *     tags={"Grupos"},
     *     summary="Deletar grupo",
     *     description="Deletar grupo",
     *     operationId="destroyGrupo",
     *     security={{"bearer":{}}},
     *     @OA\Parameter(name="id", description="id, eg; 1", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $grupo =  Grupo::find($id);
            if ($grupo == null) {
                return $this->error('Group Not Found', Response::HTTP_NOT_FOUND, null);
            }

            $deleted = $grupo->delete($id);
            if (!$deleted) {
                return $this->error('Failed to delete the grupo.', Response::HTTP_INTERNAL_SERVER_ERROR, null);
            }

            return $this->success($grupo, 'Group Deleted Successfully !');
            
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }
}
