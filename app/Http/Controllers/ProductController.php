<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Warehouse;
use App\Http\Requests\AddProductToProjectRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductQuantityRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductProject;
use App\Models\Project;
use App\Services\ChangeLogService;
use App\Services\DataTable\DataTable;
use App\Services\MultiSelectService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProductController extends Controller
{
    public ProductService $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }

    /**
     * Display a listing of the resource.
     *
     * @param  null|string $slug
     * @return Response
     */
    public function index(?string $slug = null): Response
    {
        return Inertia::render('Products/Index', [
            'dataTable'        => fn () => $this->service->getIndexMethodDatatable($slug),
            'projects'         => Inertia::lazy(fn () => (new MultiSelectService(Project::query()))->dataForSelect()),
            'projectWarehouse' => Inertia::lazy(fn () => Project::pluck('warehouse', 'id')->toArray()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreProductRequest $request
     * @return RedirectResponse
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createProduct($request);

            DB::commit();

            return redirect()->route('products.edit', ['product' => $this->service->getProduct()->id])->with('success', 'The record has been successfully created.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error creating record.']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return Response
     */
    public function show(Product $product): Response
    {
        $product->load(['changeLogsLimited', 'quantity']);

        $dataTable = (new DataTable(
            ProductProject::where('product_id', $product->id)
        ))
            ->setRelation('project', ['id', 'name', 'warehouse'])
            ->setRelation('creator')
            ->setColumn('creator.name', 'Creator', true, true)
            ->setColumn('project.name', 'Name', true, true)
            ->setColumn('project.warehouse', 'Warehouse', false, true)
            ->setColumn('quantity', 'Quantity', true, true)
            ->setColumn('created_at', 'Created', true, true)
            ->setDateColumn('created_at', 'dd.mm.YYYY H:i')
            ->setEnumColumn('project.warehouse', Warehouse::class)
            ->run();

        return Inertia::render('Products/Show', [
            'product'    => $product,
            'dataTable'  => fn () => $dataTable,
            'changeLogs' => Inertia::lazy(fn () => ChangeLogService::getChangeLogsDataTable($product)),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Product  $product
     * @return Response
     */
    public function edit(Product $product): Response
    {
        $product->load(['changeLogsLimited', 'quantity']);

        $dataTable = (new DataTable(
            ProductProject::where('product_id', $product->id)
        ))
            ->setRelation('project', ['id', 'name', 'warehouse'])
            ->setRelation('creator')
            ->setColumn('project.id', '#', true, true)
            ->setColumn('creator.name', 'Creator', true, true)
            ->setColumn('project.name', 'Name', true, true)
            ->setColumn('project.warehouse', 'Warehouse', false, true)
            ->setColumn('quantity', 'Quantity', true, true)
            ->setColumn('created_at', 'Created', true, true)
            ->setColumn('action', 'Action')
            ->setDateColumn('created_at', 'dd.mm.YYYY H:i')
            ->setEnumColumn('project.warehouse', Warehouse::class)
            ->run();

        return Inertia::render('Products/Edit', [
            'product'    => $product,
            'dataTable'  => fn () => $dataTable,
            'changeLogs' => Inertia::lazy(fn () => ChangeLogService::getChangeLogsDataTable($product)),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateProductRequest $request
     * @param  Product              $product
     * @return RedirectResponse
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->setProduct($product)->updateProduct($request);

            DB::commit();

            return back()->with('success', 'The record has been successfully updated.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error updating record.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Product          $product
     * @return RedirectResponse
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $product->delete();

            return redirect()->back()->with('success', 'The record has been successfully deleted.');
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error deleting record.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  UpdateProductQuantityRequest $request
     * @return RedirectResponse
     */
    public function updateQuantity(UpdateProductQuantityRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->updateProductQuantity($request);

            DB::commit();

            return back()->with('success', 'The record has been successfully updated.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error updating record.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AddProductToProjectRequest $request
     * @return RedirectResponse
     */
    public function addToProject(AddProductToProjectRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->addProductToProject($request);

            DB::commit();

            return back()->with('success', 'The record has been successfully updated.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors(['Error updating record.']);
        }
    }
}
