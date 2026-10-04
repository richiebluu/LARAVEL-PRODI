<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Struktur Organisasi | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Struktur organisasi Program Studi Teknologi Informasi: Koordinator Program Studi, Koordinator Gugus, dan pengelola lainnya.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body data-nav="profil">

  {{-- Navbar --}}
  @include('partials.public-navbar')

  {{-- Hero --}}
  <section class="page-hero page-hero--foto" style="{{ \App\Support\HeroFoto::style('struktur-organisasi') }}">
    <div class="container">
      <h1>{{ __('Struktur Organisasi') }}</h1>
      <p>{{ __('Pengelola Program Studi Teknologi Informasi.') }}</p>
      <div class="breadcrumb"><a href="{{ url('/') }}">{{ __('Beranda') }}</a><span class="sep">/</span><a href="{{ url('/profil') }}">{{ __('Profil') }}</a><span class="sep">/</span><span class="current">{{ __('Struktur Organisasi') }}</span></div>
    </div>
  </section>

  <section class="section-pad">
    <div class="container">
      @if ($strukturOrganisasi->isNotEmpty())
        <div class="section-head">
          <span class="eyebrow"><i class="fa-solid fa-sitemap"></i> {{ __('Struktur Organisasi') }}</span>
          <h2>{{ $prodi?->nama_prodi ?? __('Program Studi Teknologi Informasi') }}</h2>
        </div>
        <div class="org-chart-scroll">
          <div class="org-chart" role="list" aria-label="{{ __('Bagan struktur organisasi') }}">
            @foreach ($tingkatStruktur as $baris)
              @php $tingkatPertama = $loop->first; $tingkatTerakhir = $loop->last; @endphp
              <div class="org-level {{ $baris->count() === 1 ? 'org-level--tunggal' : '' }}">
                @foreach ($baris as $s)
                  <div class="org-node {{ $tingkatPertama ? '' : 'ada-atas' }} {{ $tingkatTerakhir ? '' : 'ada-bawah' }}" role="listitem">
                    @unless ($tingkatPertama)
                      <span class="org-link-up" aria-hidden="true"><i class="fa-solid fa-caret-down"></i></span>
                    @endunless
                    <div class="card person-card reveal">
                      <div class="person-photo" @if ($s->foto_url) style="background-image:url('{{ $s->foto_url }}')" @else style="background-image:url('{{ asset('images/Kodex.png') }}'); background-size:contain; background-repeat:no-repeat; background-color:var(--grey-50);" @endif>
                        @if ($tingkatPertama)<span class="person-badge">{{ $s->jabatan }}</span>@endif
                      </div>
                      <div class="person-body">
                        <h4>{{ $s->nama_pejabat }}</h4>
                        <div class="person-meta">{{ $s->jabatan }}</div>
                        @if ($s->dosen)
                          <a href="{{ url('/dosen') }}#modalDosen{{ $s->dosen->nuptk }}" class="btn btn-sm btn-outline" style="margin-top:6px;">{{ __('Lihat Detail') }}</a>
                        @endif
                      </div>
                    </div>
                    @unless ($tingkatTerakhir)
                      <span class="org-link-down" aria-hidden="true"></span>
                    @endunless
                  </div>
                @endforeach
              </div>
              @unless ($tingkatTerakhir)
                <div class="org-connector" aria-hidden="true"></div>
              @endunless
            @endforeach
          </div>
        </div>
      @else
        <div class="empty-public reveal">
          <img src="{{ asset('images/Kodex.png') }}" alt="Kodex">
          <p>{{ __('Struktur organisasi belum diisi oleh Staff Prodi.') }}</p>
        </div>
      @endif
    </div>
  </section>

  {{-- Footer --}}
  @include('partials.public-footer')
  <button class="back-to-top" aria-label="{{ __('Kembali ke atas') }}"><i class="fa-solid fa-arrow-up"></i></button>
<script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
