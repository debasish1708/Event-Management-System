<div class="d-flex align-items-center gap-2">
  <!-- View Icon -->
  <button type="button" class="p-0 border-0 bg-transparent" onclick="handleViewUser('{{ $user->id }}')"
    title="View">
    <i class="fas fa-eye text-info"></i>
  </button>

  <!-- Edit Icon -->
  <button type="button" class="p-0 border-0 bg-transparent" onclick="window.location.href='{{ route('users.edit', $user->id) }}'"
    title="Edit">
    <i class="fas fa-pencil-alt text-primary"></i>
  </button>

  <!-- Delete Icon -->
  <button type="button" class="p-0 border-0 bg-transparent"
     onclick="handleDeleteUser('{{ route('users.destroy', $user->id) }}')" title="DELETE">
     <i class="fas fa-trash-alt text-danger"></i>
   </button>
</div>