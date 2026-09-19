<?php

namespace App\Admin\Controllers;

use App\Enums\LegalDocumentType;
use App\Models\LegalDocument;
use App\Repositories\LegalDocumentRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LegalDocumentController extends AdminController
{
    protected $title = 'Юридические документы';

    protected function grid()
    {
        $grid = new Grid(new LegalDocument());

        // По умолчанию — только актуальные; архив через фильтр «Текущая = Нет»
        if (!request()->filled('is_current')) {
            $grid->model()->where('is_current', true);
        }

        $grid->model()
            ->orderBy('type')
            ->orderByDesc('version');

        $grid->column('type', __('Документ'))->display(function ($type) {
            return LegalDocumentType::LABELS[$type] ?? $type;
        });
        $grid->column('title', __('Заголовок'));
        $grid->column('version', __('Версия'))->display(function ($version) {
            $badge = $this->is_current
                ? '<span class="label label-success">v' . (int) $version . ' · текущая</span>'
                : '<span class="label label-default">v' . (int) $version . '</span>';

            return $badge;
        });
        $grid->column('versions_total', __('Всего версий'))->display(function () {
            return (string) LegalDocument::query()->where('type', $this->type)->count();
        });
        $grid->column('published_at', __('Опубликовано'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('type', 'Тип')->select(LegalDocumentType::LABELS);
            $filter->equal('is_current', 'Показать')->select([
                1 => 'Только текущие',
                0 => 'Только архивные',
            ])->default(1);
        });

        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            // Правим только текущую версию; архив — только просмотр
            if (!$actions->row->is_current) {
                $actions->disableEdit();
            }
        });
        $grid->disableBatchActions();

        $grid->tools(function ($tools) {
            $tools->append(
                '<span class="pull-right" style="margin:7px 12px 0 0;color:#777">'
                . 'В списке — актуальные версии. История открывается в карточке документа.'
                . '</span>'
            );
        });

        return $grid;
    }

    protected function detail($id)
    {
        $document = LegalDocument::findOrFail($id);
        $show = new Show($document);

        if (!$document->is_current) {
            $current = LegalDocument::query()
                ->where('type', $document->type)
                ->where('is_current', true)
                ->first();
            $currentLink = $current
                ? admin_url('legal-documents/' . $current->id)
                : null;
            $show->field('archive_notice', ' ')->unescape()->as(function () use ($currentLink) {
                $html = '<div class="alert alert-warning">Это <strong>архивная</strong> версия. На сайте показывается только текущая.';
                if ($currentLink) {
                    $html .= ' <a href="' . e($currentLink) . '">Открыть текущую →</a>';
                }
                $html .= '</div>';

                return $html;
            });
        }

        $show->field('type', __('Документ'))->as(function ($type) {
            return LegalDocumentType::LABELS[$type] ?? $type;
        });
        $show->field('version', __('Версия'))->as(function ($version) use ($document) {
            return $document->is_current
                ? 'v' . $version . ' (текущая)'
                : 'v' . $version . ' (архив)';
        });
        $show->field('title', __('Заголовок'));
        $show->field('published_at', __('Опубликовано'));
        $show->field('created_at', __('Создано'));
        $show->field('body_html', __('Содержимое'))->unescape()->as(function ($html) {
            return '<div class="legal-document-preview" style="max-width:100%;overflow:auto;border:1px solid #eee;padding:16px;background:#fff">'
                . $html
                . '</div>';
        });

        $show->field('version_history', __('История версий'))->unescape()->as(function () use ($document) {
            $versions = LegalDocument::query()
                ->where('type', $document->type)
                ->orderByDesc('version')
                ->get(['id', 'version', 'title', 'is_current', 'published_at']);

            if ($versions->isEmpty()) {
                return '<em>Нет версий</em>';
            }

            $rows = '';
            foreach ($versions as $version) {
                $url = admin_url('legal-documents/' . $version->id);
                $isActive = (int) $version->id === (int) $document->id;
                $status = $version->is_current
                    ? '<span class="label label-success">текущая</span>'
                    : '<span class="label label-default">архив</span>';
                $title = e($version->title);
                $published = optional($version->published_at)->format('d.m.Y H:i') ?: '—';
                $rowStyle = $isActive ? 'background:#f5f9ff;font-weight:600' : '';
                $link = $isActive
                    ? 'v' . (int) $version->version . ' (эта)'
                    : '<a href="' . e($url) . '">v' . (int) $version->version . '</a>';

                $rows .= '<tr style="' . $rowStyle . '">'
                    . '<td>' . $link . '</td>'
                    . '<td>' . $status . '</td>'
                    . '<td>' . $title . '</td>'
                    . '<td>' . e($published) . '</td>'
                    . '</tr>';
            }

            return '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin:0">'
                . '<thead><tr><th>Версия</th><th>Статус</th><th>Заголовок</th><th>Опубликовано</th></tr></thead>'
                . '<tbody>' . $rows . '</tbody></table></div>';
        });

        $show->panel()->tools(function ($tools) use ($document) {
            $tools->disableDelete();
            if (!$document->is_current) {
                $tools->disableEdit();
            }
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new LegalDocument());

        if ($form->isEditing()) {
            $form->select('type', __('Тип'))
                ->options(LegalDocumentType::LABELS)
                ->readOnly()
                ->help('Тип нельзя сменить. Сохранение опубликует новую версию.');
            $form->display('version', __('Версия-источник'));
            $form->html(
                '<div class="alert alert-info" style="margin-bottom:15px">Сохранение опубликует <strong>новую</strong> версию этого типа. Старая запись останется в истории.</div>'
            );
        } else {
            $form->select('type', __('Тип'))
                ->options(LegalDocumentType::LABELS)
                ->required()
                ->help('Если документ этого типа уже есть — будет создана следующая версия и станет текущей.');
        }

        $form->text('title', __('Заголовок'))->required();
        $form->htmlEditor('body_html', __('Содержимое'))
            ->required()
            ->help('Визуальный HTML-редактор. Кнопка Source — правка исходного HTML.');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }

    public function store()
    {
        return $this->publishFromRequest();
    }

    public function update($id)
    {
        $existing = LegalDocument::query()->findOrFail($id);

        if (!$existing->is_current) {
            admin_toastr('Редактировать можно только текущую версию', 'error');

            return redirect(admin_url('legal-documents/' . $id));
        }

        return $this->publishFromRequest($existing->type);
    }

    public function destroy($id)
    {
        return response('Удаление запрещено', 405);
    }

    private function publishFromRequest(?string $forcedType = null)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'body_html' => 'required|string',
        ];

        if ($forcedType === null) {
            $rules['type'] = 'required|string|in:' . implode(',', LegalDocumentType::ALL);
        }

        $data = request()->validate($rules);
        $type = $forcedType ?? $data['type'];

        $document = app(LegalDocumentRepository::class)->publishNewVersion(
            $type,
            $data['title'],
            $data['body_html']
        );

        admin_toastr('Новая версия v' . $document->version . ' опубликована');

        return redirect(admin_url('legal-documents/' . $document->id));
    }
}
