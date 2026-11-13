/* Gudang 2B · Frontend master data */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';
  const root = document.querySelector('.fe-page');
  if (!root) return;
  const tableIds = ['usersTable','supplierTable','kategoriTable','barangTable'];
  if (window.jQuery && jQuery.fn.DataTable) {
    tableIds.forEach(id => {
      if (!document.getElementById(id)) return;
      jQuery('#'+id).DataTable({pageLength:10, lengthMenu:[5,10,25,50],order:[],autoWidth:false,language:{search:'Cari:',lengthMenu:'Tampilkan _MENU_ data',info:'Menampilkan _START_–_END_ dari _TOTAL_ data',infoEmpty:'Belum ada data',zeroRecords:'Data tidak ditemukan',emptyTable:'Belum ada data',paginate:{first:'Awal',last:'Akhir',next:'Berikutnya',previous:'Sebelumnya'}}});
    });
  }
  const container = document.querySelector('.fe-page');
  container.addEventListener('click', function(event){
    const edit = event.target.closest('.fe-edit');
    if (edit) {
      const form = document.querySelector('#editModal form');
      if (!form) return;
      form.reset();
      form.action = form.dataset.actionTemplate.replace('__ID__', encodeURIComponent(edit.dataset.id));
      const editIdInput = form.querySelector('[name=_edit_id]'); if(editIdInput) editIdInput.value = edit.dataset.id;
      form.querySelectorAll('[name]').forEach(field => {
        const key = field.name.replace(/_([a-z])/g,(_,x)=>x.toUpperCase());
        if (!['_token', '_method', '_edit_id', 'password'].includes(field.name) && field.type !== 'file') field.value = edit.dataset[key] ?? '';
      });
      return;
    }
    const detail = event.target.closest('.fe-detail');
    if (detail) {
      const modal = document.querySelector('#detailModal');
      if (!modal) return;
      modal.querySelectorAll('[data-detail]').forEach(node => { const key = node.dataset.detail.replace(/-([a-z])/g,(_,x)=>x.toUpperCase()); node.textContent = detail.dataset[key] || '—'; });
      const image = modal.querySelector('#detailImage');
      if (image) { image.classList.toggle('d-none', !detail.dataset.gambar); if(detail.dataset.gambar) image.src=detail.dataset.gambar; else image.removeAttribute('src'); }
    }
  });
  document.querySelectorAll('.fe-delete-form').forEach(form => {
    form.addEventListener('submit', async function(event){
      if (!window.Swal) { if(!window.confirm('Yakin ingin menghapus data ini?')) event.preventDefault(); return; }
      event.preventDefault();
      const result = await Swal.fire({title:'Hapus data?',text:'Data '+(form.dataset.label||'ini')+' akan dihapus. Tindakan ini tidak dapat dibatalkan.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',confirmButtonColor:'#dc2626',reverseButtons:true,customClass:{popup:'fe-swal-compact'}});
      if(result.isConfirmed) form.submit();
    });
  });
  document.querySelectorAll('.fe-modal input[type=file]').forEach(field => field.addEventListener('change', function(){
    if (this.files[0] && this.files[0].size > 2*1024*1024) { this.value=''; if(window.Swal) Swal.fire({icon:'error',title:'Foto terlalu besar',text:'Ukuran gambar maksimal 2 MB.',confirmButtonText:'Mengerti',customClass:{popup:'fe-swal-compact'}}); else alert('Ukuran gambar maksimal 2 MB.'); }
  }));
  document.querySelectorAll('.fe-flash').forEach(node => {
    const type=node.dataset.type, message=node.dataset.message==='The email has already been taken.' ? 'Alamat email sudah terdaftar. Gunakan email lain.' : node.dataset.message;
    if(window.Swal) Swal.fire({toast:true,position:'top-end',icon:type,title:message,showConfirmButton:false,timer:4200,timerProgressBar:true,customClass:{popup:'fe-swal-toast'}});
    else console.info(message);
  });
  // Restore non-sensitive form values after Laravel validation errors.
  const oldType = document.querySelector('.fe-validation-context');
  if (oldType && window.bootstrap) {
    const dialog = document.getElementById(oldType.dataset.modal);
    if (dialog) {
      const form = dialog.querySelector('form');
      if (oldType.dataset.editId) {
        form.action = form.dataset.actionTemplate.replace('__ID__', encodeURIComponent(oldType.dataset.editId));
        const editIdInput=form.querySelector('[name=_edit_id]'); if(editIdInput) editIdInput.value=oldType.dataset.editId;
      }
      oldType.querySelectorAll('[data-field]').forEach(node => {
        const input=form.querySelector('[name="'+node.dataset.field+'"]');
        if (input && input.type !== 'file' && input.type !== 'password') input.value=node.dataset.value || '';
      });
      bootstrap.Modal.getOrCreateInstance(dialog).show();
    }
  }
  // FE 1 validation hotfix: show field-specific messages without changing backend rules.
  const humanFields = {name:'Nama lengkap',email:'Email',password:'Password',role:'Peran pengguna',nama_supplier:'Nama supplier',telepon:'Telepon',alamat:'Alamat',nama_kategori:'Nama kategori',deskripsi:'Deskripsi',kode_barang:'Kode barang',nama_barang:'Nama barang',kategori_id:'Kategori',satuan:'Satuan',stok_minimum:'Stok minimum',gambar:'Gambar',keterangan:'Keterangan'};
  const labelOf = input => humanFields[input.name] || input.name.replace(/_/g, ' ');
  function clearErrors(form) {
    form.querySelectorAll('.fe-field-error,.fe-validation-summary').forEach(n=>n.remove());
    form.querySelectorAll('.is-invalid').forEach(n=>{n.classList.remove('is-invalid');n.removeAttribute('aria-invalid');});
  }
  function setError(input, message) {
    if (!input) return;
    input.classList.add('is-invalid');
    input.setAttribute('aria-invalid','true');
    const holder=input.closest('.mb-3') || input.parentElement;
    const n=document.createElement('div');n.className='fe-field-error';n.setAttribute('role','alert');n.textContent=message;holder.appendChild(n);
  }
  function nativeError(input) {
    const name=labelOf(input);
    if(input.validity.valueMissing)return name+' wajib diisi.';
    if(input.validity.typeMismatch)return 'Format '+name.toLowerCase()+' tidak valid.';
    if(input.validity.tooShort)return name+' minimal '+input.minLength+' karakter.';
    if(input.validity.tooLong)return name+' maksimal '+input.maxLength+' karakter.';
    if(input.validity.rangeUnderflow)return name+' minimal '+input.min+'.';
    if(input.validity.patternMismatch)return name==='Password'?'Password minimal 6 karakter dan harus mengandung angka.':name+' tidak sesuai format yang diminta.';
    return name+' tidak valid.';
  }
  document.querySelectorAll('.fe-modal form').forEach(form=>{
    // Turn off browser speech bubbles and render consistent messages in Indonesian.
    form.noValidate=true;
    form.addEventListener('input',e=>{const f=e.target;if(f.classList?.contains('is-invalid')){f.classList.remove('is-invalid');f.removeAttribute('aria-invalid');f.closest('.mb-3')?.querySelectorAll('.fe-field-error').forEach(n=>n.remove());}});
    form.addEventListener('change',e=>{const f=e.target;if(f.classList?.contains('is-invalid')){f.classList.remove('is-invalid');f.removeAttribute('aria-invalid');f.closest('.mb-3')?.querySelectorAll('.fe-field-error').forEach(n=>n.remove());}});
    form.addEventListener('submit',e=>{
      clearErrors(form);let first=null;
      [...form.elements].forEach(input=>{if(!input.willValidate || input.checkValidity())return;const m=nativeError(input);setError(input,m);if(!first)first={input,message:m};});
      if(first){e.preventDefault();first.input.focus();first.input.scrollIntoView({block:'nearest',behavior:'smooth'});if(window.Swal)Swal.fire({icon:'warning',title:'Periksa formulir',text:first.message,confirmButtonText:'Mengerti',customClass:{popup:'fe-swal-compact'}});}
    });
  });
  // Render Laravel validation errors inline after redirect (e.g. duplicate email).
  const serverErrors=document.querySelectorAll('.fe-server-errors [data-error-field]');
  if(serverErrors.length){
    const ctx=document.querySelector('.fe-validation-context');
    const form=document.querySelector('#'+(ctx?.dataset.modal||'addModal')+' form');
    if(form){
      clearErrors(form);
      serverErrors.forEach(node=>{const msg=node.dataset.errorMessage==='The email has already been taken.'?'Alamat email sudah terdaftar. Gunakan email lain.':node.dataset.errorMessage;setError(form.querySelector('[name="'+CSS.escape(node.dataset.errorField)+'"]'),msg);});
      const summary=document.createElement('div');summary.className='fe-validation-summary';summary.setAttribute('role','alert');summary.textContent='Data belum tersimpan. Periksa kolom yang ditandai di bawah.';form.querySelector('.modal-body')?.prepend(summary);
    }
  }

});
