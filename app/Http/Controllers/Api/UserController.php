<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
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
     *     path="/api/usuarios",
     *     tags={"Usuários"},
     *     summary="Obter a lista de usuários cadastrados",
     *     description="Obter a lista de usuários cadastrados",
     *     operationId="index",
     *     security={{"bearer":{}}},
     *     @OA\Response(response=200,description="OK"),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $data = User::all();
            return $this->success($data, 'Lista de usuários gerada com sucesso!');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    
    /**
     * @OA\POST(
     *     path="/api/usuarios",
     *     tags={"Usuários"},
     *     summary="Cadastrar novo usuário",
     *     description="Cadastrar novo usuário",
     *     operationId="store",
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="nome", type="string", example="nome"),
     *              @OA\Property(property="email", type="string", example="nome.sobrenome@dominio.com"),
     *              @OA\Property(property="usuario", type="string", example="nome.sobrenome"),
     *              @OA\Property(property="password", type="string", example="123456"),
     *              @OA\Property(property="status", type="string", example="1"),
     *              @OA\Property(property="id_setor", type="string", example="1"),
     *              @OA\Property(property="id_grupo", type="string", example="1"),
     *          ),
     *      ),
     *      security={{"bearer":{}}},
     *      @OA\Response(response=200, description="OK" ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            try{
                $attr = $request->validate([
                    'nome' => 'required|string|max:255',
                    'email' => 'required|string|email',
                    'usuario' => 'required|string|unique:users,usuario',
                    'password' => 'required|string|min:6',
                    'status' => 'required|integer',
                    'id_setor' => 'required|integer',
                    'id_grupo' => 'required|integer'
                ]);
            }catch (ValidationException $e) {
                $errors = $e->errors();
                return response()->json(['errors' => $errors], 422);
            }
            
            $data = [
                'nome' => $attr['nome'],
                'email' => $attr['email'],
                'password' => Hash::make($attr['password']),
                'usuario' => $attr['usuario'],
                'status' => $attr['status'],
                'remember_token' => Str::random(10),
                'id_grupo' => $attr['id_grupo'],
                'id_setor' => $attr['id_setor'],
            ];
            $usuario = User::create($data);
            return $this->success($usuario, 'Usuário cadastrado com sucesso!');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * @OA\GET(
     *     path="/api/usuarios/{id}",
     *     tags={"Usuários"},
     *     summary="Exibir detalhes de um usuário específico.",
     *     description="Exibir detalhes de um usuário específico.",
     *     operationId="show",
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
            $data = User::find($id);
            if (is_null($data)) {
                return $this->error('User Not Found', Response::HTTP_NOT_FOUND, null);
            }

            return $this->success($data, 'User Details Fetch Successfully !');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/usuarios/{id}",
     *     tags={"Usuários"},
     *     summary="Editar usuário",
     *     description="Editar usuário",
     *     @OA\Parameter(name="id", description="id, eg; 1", required=true, in="path", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="nome", type="string", example="nome"),
     *              @OA\Property(property="email", type="string", example="nome.sobrenome@dominio.com"),
     *              @OA\Property(property="password", type="string", example="123456"),
     *              @OA\Property(property="status", type="string", example="1"),
     *              @OA\Property(property="id_setor", type="string", example="1"),
     *              @OA\Property(property="id_grupo", type="string", example="1"),
     *          ),
     *      ),
     *     operationId="update",
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
                    'nome' => 'required|string|max:255',
                    'email' => 'required|string|email',
                    'password' => 'required|string|min:6',
                    'status' => 'required|integer',
                    'id_setor' => 'required|integer',
                    'id_grupo' => 'required|integer'
                ]);
            }catch (ValidationException $e) {
                $errors = $e->errors();
                return response()->json(['errors' => $errors], 422);
            }

            $data = [
                'nome' => $attr['nome'],
                'email' => $attr['email'],
                'password' => Hash::make($attr['password']),
                'status' => $attr['status'],
                'remember_token' => Str::random(10),
                'id_grupo' => $attr['id_grupo'],
                'id_setor' => $attr['id_setor'],
            ];

            $user = User::find($id);

            if ($user){
                $user->update($data);
                return $this->success($data, 'User Updated Successfully !');
            }else{
                return $this->error('User Not Found', Response::HTTP_NOT_FOUND, null);
            }

            
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }

    /**
     * @OA\DELETE(
     *     path="/api/usuarios/{id}",
     *     tags={"Usuários"},
     *     summary="Deletar Usuário",
     *     description="Deletar Usuário",
     *     operationId="destroy",
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
            $user =  User::find($id);
            if ($user == null) {
                return $this->error('User Not Found', Response::HTTP_NOT_FOUND, null);
            }

            $deleted = $user->delete($id);
            if (!$deleted) {
                return $this->error('Failed to delete the user.', Response::HTTP_INTERNAL_SERVER_ERROR, null);
            }

            return $this->success($user, 'User Deleted Successfully !');
            
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, null);
        }
    }
}
