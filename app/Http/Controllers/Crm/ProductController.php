<?php

namespace App\Http\Controllers\Crm;

use App\Domain\Crm\Actor;
use App\Domain\Crm\CreateProduct;
use App\Domain\Crm\CrmRepository;
use App\Domain\Crm\DeleteProduct;
use App\Domain\Crm\Product;
use App\Domain\Crm\UpdateProduct;
use App\Domain\Crm\User;
use App\Http\ActingUser;
use App\Http\CrmLog;
use App\Http\Export\CsvResponse;
use App\Http\Export\PdfResponse;
use App\Http\ListQuery;
use App\Http\Props\ProductListPageProps;
use App\Http\Props\ProductPageProps;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\DeleteProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Policies\CrmPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ProductController
{
    public function index(Request $request, CrmRepository $crm, ActingUser $acting): Response
    {
        [$products, $count, $page, $term, $hasMore] = $this->page($request, $crm, $acting);

        return Inertia::render('Products/Index', ProductListPageProps::make($products, $count, $page, $term, $hasMore));
    }

    public function store(CreateProductRequest $request, CreateProduct $action): RedirectResponse
    {
        $action->execute(
            new Actor($request->actor()->id),
            (string) $request->validated('name'),
            (int) $request->validated('unit_price'),
        );
        CrmLog::write($request, 'F16', 'success', null);

        return redirect()->route('products.index');
    }

    public function show(Request $request, int $product, CrmRepository $crm, ActingUser $acting): Response
    {
        $found = $this->visible($product, $crm, $acting->resolve($request));

        return Inertia::render('Products/Show', ProductPageProps::make($found));
    }

    public function update(UpdateProductRequest $request, int $product, UpdateProduct $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($product, $crm, $request->actor());
        $action->execute(
            new Actor($request->actor()->id),
            $product,
            (string) $request->validated('name'),
            (int) $request->validated('unit_price'),
        );
        CrmLog::write($request, 'F20', 'success', null);

        return redirect()->route('products.show', $product);
    }

    public function destroy(DeleteProductRequest $request, int $product, DeleteProduct $action, CrmRepository $crm): RedirectResponse
    {
        $this->visible($product, $crm, $request->actor());
        $action->execute(new Actor($request->actor()->id), $product);
        CrmLog::write($request, 'F37', 'success', null);

        return redirect()->route('products.index');
    }

    public function csv(Request $request, CrmRepository $crm, ActingUser $acting): StreamedResponse
    {
        $term = ListQuery::term($request);
        $products = $acting->resolve($request) === null
            ? []
            : $crm->searchProducts($term, ListQuery::EXPORT_LIMIT, 0);

        return CsvResponse::make('products.csv', ['name', 'unit_price'], array_map(
            fn (Product $product) => [$product->name, $product->unitPrice],
            $products,
        ));
    }

    public function pdf(Request $request, int $product, CrmRepository $crm, ActingUser $acting): \Illuminate\Http\Response
    {
        $found = $this->visible($product, $crm, $acting->resolve($request));

        return PdfResponse::make('product-'.$found->id.'.pdf', $found->name, [
            'Name' => $found->name,
            'Unit price' => (string) $found->unitPrice,
        ]);
    }

    /**
     * @return array{0: list<Product>, 1: int, 2: int, 3: string, 4: bool}
     */
    private function page(Request $request, CrmRepository $crm, ActingUser $acting): array
    {
        $term = ListQuery::term($request);
        $page = ListQuery::page($request);
        if ($acting->resolve($request) === null) {
            return [[], 0, $page, $term, false];
        }

        $rows = $crm->searchProducts($term, ListQuery::SIZE + 1, ListQuery::offset($page));
        $hasMore = count($rows) > ListQuery::SIZE;

        return [array_slice($rows, 0, ListQuery::SIZE), $crm->countProducts(), $page, $term, $hasMore];
    }

    private function visible(int $product, CrmRepository $crm, ?User $actor): Product
    {
        $found = $crm->findProduct($product);
        if ($found === null || ! app(CrmPolicy::class)->viewProduct($actor, $found)) {
            abort(404);
        }

        return $found;
    }
}
