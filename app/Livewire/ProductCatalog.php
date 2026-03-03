<?php
declare(strict_types=1);
namespace App\Livewire;

use App\Data\ProductCollectionData;
use App\Data\ProductData;
use App\Models\Product;
use App\Models\product as ModelsProduct;
use App\Models\Tag;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public $queryString = [
        'select_collections' => ['except' => []],
        'sort_by' => ['except' => 'newest'],
        'search'  => ['except' => []]
    ];

    public array $select_collections = [];

    public string $search = '';

    public string $sort_by = 'newest'; //latest, price_asc, price_desc

    public function mount()
    {
        $this->validate();
    }

    //validasi untuk inputan user anomali(hacker)
    protected function rules()
    {
        return [
            'select_collections' => 'array',
            'select_collections.*' => 'integer|exists:tags,id',
            'search' => 'nullable|string|min:3|max:30',
            'sort_by' => 'in:newest,latest,price_asc,price_desc'
        ];
    }

    public function applyFilters()
    {
        $this->validate();
        
        $this->resetPage();
    }

    //validasi untuk fungsi reset
    public function resetFilters()
    {
        $this->select_collections = [];
        $this->search = '';
        $this->sort_by = 'newest';
        $this->resetErrorBag();
        $this->resetPage();
    }

    //fungsi untuk rander
    public function render()
    {
        $collections = ProductCollectionData::collect([]);
        $products = ProductData::collect([]);
        //early return
        if($this->getErrorBag()->isNotEmpty()){
            return view('livewire.product-catalog', compact('collections', 'products'));
        }

        $collection_result = Tag::query()->withType('collection')->withCount('products')->get();
        // $result = Product::paginate(1); //ORM  atau DATABASE QUERY
        $query = Product::query();

        if($this->search){
            $query->where('name', 'LIKE', "%{$this->search}%");
        }

        if (!empty($this->select_collections)){
            $query->whereHas('tags', function($query){
                $query->whereIn('id', $this->select_collections);
            });
        }

        switch($this->sort_by) {
            case 'latest':
                $query->oldest();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }
        
        $products = ProductData::collect(
            $query->paginate(9)
        );
        $collections = ProductCollectionData::collect($collection_result);

        return view('livewire.product-catalog', compact('products', 'collections'));
    }
}
