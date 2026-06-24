<div id="uploadModalEmpresarial" class="fixed inset-0 flex items-center justify-center z-[9999] hidden bg-black/60">
    <div class="w-full max-w-md mx-4 rounded-xl overflow-hidden shadow-2xl" style="background:#1e1e1e;border:1px solid #2a3d55;">
        <div class="flex items-center justify-between px-5 py-3" style="background:#0e1a28;border-bottom:1px solid #2a3d55;">
            <h5 class="text-xs font-bold uppercase tracking-widest" style="color:#e0e0e0;">Upload Baixas Empresarial</h5>
            <button type="button" class="close-modal-empresarial text-2xl font-bold leading-none transition" style="color:#888;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#888'">&times;</button>
        </div>
        <div class="px-5 py-5">
            <form action="" method="POST" name="formulario_atualizar_empresarial" id="formulario_atualizar_empresarial" enctype="multipart/form-data">
                @csrf
                <label for="arquivo_atualizar_empresarial" class="block text-[10px] font-bold uppercase tracking-widest mb-2" style="color:#3d7ab5;">Arquivo</label>
                <input type="file" name="arquivo_atualizar_empresarial" id="arquivo_atualizar_empresarial" class="block w-full text-sm rounded-lg px-3 py-2 cursor-pointer" style="background:#141414;border:1px solid #2a3d55;color:#e0e0e0;">
            </form>
        </div>
    </div>
</div>
