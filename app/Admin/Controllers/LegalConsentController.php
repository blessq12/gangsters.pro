<?php

namespace App\Admin\Controllers;

use App\Enums\LegalConsentType;
use App\Enums\LegalDocumentType;
use App\Models\LegalConsent;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LegalConsentController extends AdminController
{
    protected $title = 'Согласия';

    protected function grid()
    {
        $grid = new Grid(new LegalConsent());

        $grid->model()
            ->with(['user', 'order', 'document'])
            ->orderByDesc('accepted_at');

        $grid->column('id', '№');
        $grid->column('consent_type', 'Тип согласия')->display(function ($type) {
            return LegalConsentType::label((string) $type);
        });
        $grid->column('user_id', 'Пользователь')->display(function ($userId) {
            if (!$userId) {
                return 'Гость';
            }
            $user = $this->user;
            if (!$user) {
                return 'Пользователь №' . $userId;
            }
            $parts = array_filter([$user->name, $user->tel, $user->email]);

            return $parts !== [] ? implode(' · ', $parts) : 'Пользователь №' . $userId;
        });
        $grid->column('order_id', 'Заказ')->display(function ($orderId) {
            return $orderId ? 'Заказ №' . $orderId : '—';
        });
        $grid->column('document.type', 'Документ')->display(function ($type) {
            return LegalDocumentType::LABELS[$type] ?? ($type ?: '—');
        });
        $grid->column('document.version', 'Версия документа')->display(function ($version) {
            return $version !== null ? 'Версия ' . $version : '—';
        });
        $grid->column('ip', 'IP-адрес')->display(function ($ip) {
            return $ip ?: '—';
        });
        $grid->column('accepted_at', 'Дата и время принятия')->display(function ($value) {
            return $value ? date('d.m.Y H:i', strtotime((string) $value)) : '—';
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('consent_type', 'Тип согласия')->select(LegalConsentType::LABELS);
            $filter->equal('document.type', 'Тип документа')->select(LegalDocumentType::LABELS);
            $filter->equal('order_id', 'Номер заказа');
            $filter->equal('user_id', 'Номер пользователя');
            $filter->between('accepted_at', 'Период принятия')->datetime();
        });

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->tools(function ($tools) {
            $tools->append(
                '<span class="pull-right" style="margin:7px 12px 0 0;color:#777">'
                . 'Журнал зафиксированных согласий. Только просмотр.'
                . '</span>'
            );
        });

        return $grid;
    }

    protected function detail($id)
    {
        $consent = LegalConsent::query()
            ->with(['user', 'order', 'document'])
            ->findOrFail($id);

        $show = new Show($consent);

        $show->field('id', 'Номер записи');
        $show->field('consent_type', 'Тип согласия')->as(function ($type) {
            return LegalConsentType::label((string) $type);
        });
        $show->field('user_id', 'Пользователь')->as(function ($userId) use ($consent) {
            if (!$userId) {
                return 'Гость (без учётной записи)';
            }
            $user = $consent->user;
            if (!$user) {
                return 'Пользователь №' . $userId . ' (запись не найдена)';
            }

            return trim(sprintf(
                '№%s · %s · %s · %s',
                $user->id,
                $user->name ?: 'без имени',
                $user->tel ?: 'без телефона',
                $user->email ?: 'без почты'
            ));
        });
        $show->field('order_id', 'Заказ')->as(function ($orderId) {
            return $orderId ? 'Заказ №' . $orderId : 'Не привязан к заказу';
        });
        $show->field('document.type', 'Тип документа')->as(function ($type) {
            return LegalDocumentType::LABELS[$type] ?? ($type ?: '—');
        });
        $show->field('document.version', 'Версия документа')->as(function ($version) {
            return $version !== null ? 'Версия ' . $version : '—';
        });
        $show->field('document.title', 'Заголовок документа');
        $show->field('legal_document_id', 'Номер документа в базе');
        $show->field('ip', 'IP-адрес')->as(function ($ip) {
            return $ip ?: 'Не зафиксирован';
        });
        $show->field('user_agent', 'Браузер / устройство')->as(function ($ua) {
            return $ua ?: 'Не зафиксирован';
        });
        $show->field('accepted_at', 'Дата и время принятия')->as(function ($value) {
            return $value ? date('d.m.Y H:i:s', strtotime((string) $value)) : '—';
        });
        $show->field('created_at', 'Запись создана')->as(function ($value) {
            return $value ? date('d.m.Y H:i:s', strtotime((string) $value)) : '—';
        });

        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
        });

        return $show;
    }

    protected function form()
    {
        abort(405, 'Согласия доступны только для просмотра');
    }
}
