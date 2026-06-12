<?php

namespace App\Libraries;

use App\Models\ClienteModel;

class ClienteScope
{
    private ClienteModel $clientes;

    public function __construct()
    {
        $this->clientes = new ClienteModel();
    }

    public function availableForCurrentUser(): array
    {
        if (! session('isLoggedIn')) {
            return [];
        }

        if ((bool) session('es_superadmin')) {
            return $this->clientes
                ->where('estado', 'activo')
                ->orderBy('nombre', 'ASC')
                ->findAll();
        }

        $ids = [];
        foreach ((array) session('roles_full') as $rol) {
            if (! empty($rol['id_cliente'])) {
                $ids[] = (int) $rol['id_cliente'];
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return [];
        }

        return $this->clientes
            ->whereIn('id_cliente', $ids)
            ->where('estado', 'activo')
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    public function active(): ?array
    {
        $idCliente = $this->activeId();
        if ($idCliente === null) {
            return null;
        }

        return [
            'id_cliente' => $idCliente,
            'nombre'     => (string) session('cliente_activo_nombre'),
            'logo'       => session('cliente_activo_logo'),
        ];
    }

    public function activeId(): ?int
    {
        $idCliente = session('cliente_activo');

        return $idCliente === null ? null : (int) $idCliente;
    }

    public function syncActiveSession(): void
    {
        $idCliente = $this->activeId();
        if ($idCliente !== null && $this->canAccess($idCliente)) {
            return;
        }

        $this->clearActive();

        $available = $this->availableForCurrentUser();
        if (count($available) === 1) {
            $this->setActive((int) $available[0]['id_cliente']);
        }
    }

    public function setActive(int $idCliente): ?array
    {
        if (! $this->canAccess($idCliente)) {
            return null;
        }

        $cliente = $this->clientes
            ->where('estado', 'activo')
            ->find($idCliente);

        if ($cliente === null) {
            return null;
        }

        session()->set([
            'cliente_activo'        => (int) $cliente['id_cliente'],
            'cliente_activo_nombre' => $cliente['nombre'],
            'cliente_activo_logo'   => $cliente['logo'] ?? null,
        ]);

        return $cliente;
    }

    public function clearActive(): void
    {
        session()->remove(['cliente_activo', 'cliente_activo_nombre', 'cliente_activo_logo']);
    }

    public function canAccess(int $idCliente): bool
    {
        if (! session('isLoggedIn')) {
            return false;
        }

        if ((bool) session('es_superadmin')) {
            return $this->clientes
                ->where('estado', 'activo')
                ->where('id_cliente', $idCliente)
                ->first() !== null;
        }

        foreach ((array) session('roles_full') as $rol) {
            if ((int) ($rol['id_cliente'] ?? 0) === $idCliente) {
                return $this->clientes
                    ->where('estado', 'activo')
                    ->where('id_cliente', $idCliente)
                    ->first() !== null;
            }
        }

        return false;
    }
}
