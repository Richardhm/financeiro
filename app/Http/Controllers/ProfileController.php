<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\CidadeCodigoVendedor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function listar()
    {
        return view('profile.index');
    }

    public function listUser(Request $request)
    {
        $users = User::select('name', 'id', 'image', 'email', 'celular', 'ativo', 'clt', 'tipo_contrato')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderByDesc('id')
            ->get();

        return response()->json($users);
    }

    public function storeUser(Request $request)
    {
        $logo = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $location = 'users';
            $file->move($location, $filename);
            $logo = $location . '/' . $filename;
        }

        $tipoContrato = in_array($request->tipo_contrato, ['clt', 'pj', 'parceiro'])
            ? $request->tipo_contrato
            : 'pj';

        $data = [
            'name'            => $request->nome,
            'email'           => $request->email,
            'celular'         => $request->celular,
            'codigo_vendedor' => $request->codigo_vendedor,
            'uf_preferencia'  => 'GO',
            'cargo_id'        => 2,
            'password'        => bcrypt('12345678'),
            'tipo_contrato'   => $tipoContrato,
            'clt'             => $tipoContrato === 'clt' ? 1 : 0,
        ];

        if ($logo) {
            $data['image'] = $logo;
        }

        $data['corretora_id'] = 1;

        $user = User::updateOrCreate(
            ['email' => $request->email],
            $data
        );

        $tabela_origens_ids = json_decode($request->corretoras, true);
        $codigo_vendedores = json_decode($request->codigos, true);
        $codigo_tabela_origens = json_decode($request->codigo_tabela_origens, true);

        if (!empty($tabela_origens_ids) && !empty($codigo_vendedores) && !empty($codigo_tabela_origens)) {
            foreach ($tabela_origens_ids as $index => $tabela_origens_id) {
                if (isset($codigo_vendedores[$index]) && isset($codigo_tabela_origens[$index])) {
                    DB::table('cidade_codigo_vendedores')->insert([
                        'codigo_tabela_origem' => $codigo_tabela_origens[$index],
                        'codigo_vendedor' => $codigo_vendedores[$index],
                        'tabela_origens_id' => $tabela_origens_id,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => $user->wasRecentlyCreated ? 'Usuário cadastrado com sucesso!' : 'Usuário atualizado com sucesso!',
            'user' => $user
        ]);
    }

    public function destroyUser(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id'
        ]);

        $user = User::find($request->id);
        if ($user) {
            if ($user->image) {
                $imagePath = public_path($user->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $user->delete();
            return true;
        }

        return response()->json(['message' => 'Usuário não encontrado.'], 404);
    }

    public function alterarUser(Request $request)
    {
        $user = User::find($request->id);
        $user->ativo = $request->ativo == 'true' ? 1 : 0;
        $user->save();
    }

    public function alterarUserCLT(Request $request)
    {
        $tipo = in_array($request->tipo_contrato, ['clt', 'pj', 'parceiro'])
            ? $request->tipo_contrato
            : 'pj';

        $user = User::find($request->id);
        $user->tipo_contrato = $tipo;
        $user->clt           = $tipo === 'clt' ? 1 : 0;
        $user->save();

        return response()->json(['ok' => true, 'tipo_contrato' => $tipo]);
    }

    public function show(Request $request)
    {
        return User::where('id', $request->id)->with(['codigo', 'codigo.cidade'])->first();
    }

    public function excluir(Request $request)
    {
        $del = DB::table('cidade_codigo_vendedores')->where('id', $request->id)->delete();
        return $del ? 'sucesso' : 'error';
    }

    public function atualizarCodigo(Request $request)
    {
        DB::table('cidade_codigo_vendedores')
            ->where('id', $request->id)
            ->update(['codigo_vendedor' => $request->codigo_vendedor]);

        return response()->json(['ok' => true]);
    }

    public function perfil(Request $request)
    {
        return view('profile.atualizar');
    }

    public function alterar(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'celular' => 'required|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->hasFile('image')) {
            if ($user->image && file_exists(public_path($user->image))) {
                unlink(public_path($user->image));
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('users'), $imageName);
            $user->image = 'users/' . $imageName;
        }

        $user->name = $request->input('name');
        $user->celular = $request->input('celular');

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }
}
