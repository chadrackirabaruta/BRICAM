@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle mb-3 d-flex justify-content-between align-items-center">
    <div>
      <h1>
        <i class="bi bi-person"></i> Ibyakozwe na <strong>{{ $employee->name }}</strong>
        - <strong>{{ $dateLabel }}</strong>
      </h1>
    </div>
    <div>
      <a href="{{ route('transport-records.index', ['employee_id' => $employee->id]) }}"
         class="btn btn-sm btn-outline-secondary ms-2">
        <i class="bi bi-arrow-left"></i> Subira inyuma
      </a>
    </div>
  </div>

  @include('theme.success')

  <section class="section">
    @if($records->count())
      <div class="row gy-3">
        @foreach($records as $r)
        <div class="col-md-6">
          <div class="card border-left border-primary shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="card-title text-primary">{{ $r->category->name }}</h5>
                <div>
                  <!-- ✏️ Edit -->
                  <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $r->id }}">
                    <i class="bi bi-pencil"></i>
                  </button>

                  <!-- 🗑️ Delete -->
                  <form action="{{ route('transport-records.destroy', ['transport_record' => $r->id]) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Urashaka koko gusiba iyi record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </div>

              <ul class="list-group mb-2">
                <li class="list-group-item"><strong>Umubare:</strong> {{ $r->quantity }}</li>
                <li class="list-group-item"><strong>Igiciro kuri kimwe:</strong> {{ number_format($r->unit_price) }} Rwf</li>
                <li class="list-group-item"><strong>Igiteranyo:</strong> <span class="text-success fw-bold">{{ number_format($r->total_price) }} Rwf</span></li>
                <li class="list-group-item"><strong>Aho byakorewe:</strong> {{ $r->destination ?? '-' }}</li>
                @if($r->requires_stock_type)
                <li class="list-group-item"><strong>Ubwoko bwa Stock:</strong> {{ $r->stockType->name ?? '-' }}</li>
                @endif
                <li class="list-group-item"><strong>Itariki:</strong> {{ $r->transport_date }}</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- 🧩 Edit Modal -->
        <div class="modal fade" id="editModal{{ $r->id }}" tabindex="-1" aria-labelledby="editLabel{{ $r->id }}" aria-hidden="true">
          <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('transport-records.update', $r->id) }}">
              @csrf
              @method('PUT')
              <div class="modal-header">
                <h5 class="modal-title" id="editLabel{{ $r->id }}">Hindura Igikorwa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <input type="hidden" name="transport_date" value="{{ $r->transport_date }}">

                <div class="mb-3">
                  <label class="form-label">Icyakozwe</label>
                  <select name="transport_category_id" class="form-select" required>
                    @foreach($categories as $cat)
                      <option value="{{ $cat->id }}" @if($cat->id == $r->transport_category_id) selected @endif>
                        {{ $cat->name }} - {{ number_format($cat->unit_price) }} Rwf
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Umubare</label>
                  <input type="number" name="quantity" class="form-control" value="{{ $r->quantity }}" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Aho byakorewe</label>
                  <input type="text" name="destination" class="form-control" value="{{ $r->destination }}">
                </div>

                <div class="mb-3">
                  <label class="form-label">Ubwoko bwa Stock</label>
                  <select name="brick_status" class="form-select">
                    <option value="">-- Hitamo --</option>
                    @foreach($stockTypes as $stock)
                      <option value="{{ $stock->id }}" @if($r->brick_status == $stock->id) selected @endif>
                        {{ $stock->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-primary" type="submit">
                  <i class="bi bi-save"></i> Bika Impinduka
                </button>
              </div>
            </form>
          </div>
        </div>
        @endforeach

        <!-- TOTAL CARD -->
        <div class="col-12">
          <div class="card bg-light shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
              <h5 class="mb-0 fw-bold text-blue">IGITERANYO CY’AMAFARANGA:</h5>
              <span class="display-6 text-success">{{ number_format($total) }} Rwf</span>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="alert alert-warning mt-4">
        Nta bikorwa byasanzwe by'uyu mukozi kuri <strong>{{ $dateLabel }}</strong>.
      </div>
    @endif
  </section>
</main>

@include('theme.footer')
