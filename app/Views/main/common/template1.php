<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor">

<!-- begin:: Subheader -->
<div class="kt-subheader kt-grid__item" id="kt_subheader">
    <div class="kt-subheader__main">
        <h3 class="kt-subheader__title"><?= $titulo; ?></h3>
        <div class="kt-subheader__breadcrumbs">
            <a href="#" class="kt-subheader__breadcrumbs-home">
                <i class="flaticon2-shelter"></i>
            </a>

            <span class="kt-subheader__breadcrumbs-separator"></span>

            <a href="" class="kt-subheader__breadcrumbs-link">
                <?= $titulo; ?>
            </a>

            <span class="kt-subheader__breadcrumbs-separator"></span>

            <a href="" class="kt-subheader__breadcrumbs-link">
                <?= $subtitulo; ?>
            </a>
        </div>
    </div>
</div>
<!-- end:: Subheader -->

<!-- begin:: Content -->
<div class="kt-content kt-grid__item">

<!--Begin::Search-->
<div class="kt-portlet" data-ktportlet="true" id="tool_search">

    <div class="kt-portlet__head">
        <div class="kt-portlet__head-label">
            <h3 class="kt-portlet__head-title">
                <span class="kt-portlet__head-icon">
                    <i class="flaticon2-search-1"></i>
                </span>
                <?= $titulo_search; ?>
            </h3>
        </div>

        <div class="kt-portlet__head-toolbar">
            <div class="kt-portlet__head-group">
                <a href="#" data-ktportlet-tool="toggle"
                   class="btn btn-sm btn-icon btn-default btn-pill btn-icon-md">
                    <i class="la la-angle-down"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="kt-portlet__body">

        <form class="kt-form kt-form--label-right" id="form">

            <div class="row">

                <div class="col-lg-1">
                    <div class="form-group">
                        <label for="periodo">Año</label>
                        <select class="form-control form-control-sm" name="anio" id="anio">                    
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group">
                        <label for="tipo">Formato</label>
                        <select class="form-control form-control-sm" name="formato" id="formato">
                            <option value="">Seleccione</option>
                            <option value="1">Grande</option>
                            <option value="2">Pequeño</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group">
                        <label for="plantilla">Plantilla</label>
                        <select class="form-control form-control-sm" name="plantilla" id="plantilla">
                            <option value="">Seleccione</option>
                            <option value="1">Modos de producción</option>
                            <option value="2">Modos de producción - Paneles</option>                            
                            <option value="3">Departamentos DNCN</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group">
                        <label for="nivel">Nivel</label>
                        <select class="form-control form-control-sm" name="nivel" id="nivel">                         
                        </select>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="form-group">
                        <label for="dnivel">&nbsp;</label>
                        <select class="form-control form-control-sm" name="dnivel" id="dnivel" disabled>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-sm-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-success btn-sm" id="ExpExcel">
                                <i class="fa fa-file-excel"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>
<!--End::Search-->
</div>
<!-- end:: Content -->

</div>

