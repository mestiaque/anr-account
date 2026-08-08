@extends('printMaster')
@section('title', 'Daily Factory Expenditure Statement')
@section('contents')
    @if($expenses)



    @php
        $items = $expenseTypes;

        // প্রথমে শুধু সেগুলো নিন যাদের amount > 0
        $filteredItems = $items->filter(function($item) use ($expenses){
            return $expenses->where('category_id', $item->id)->sum('amount') > 0;
        })->values();
        $filteredItems->push((object)[
            'id' => 'supplierbill',
            'name' => 'Creditor Bill',
            'amount' => $supplierBill // this can be 0 if no transactions
        ]);

        $count = $filteredItems->count();
        $half = ceil($count / 2);

        $leftItems  = $filteredItems->slice(0, $half)->values();
        $rightItems = $filteredItems->slice($half)->values();

        $leftSubTotal = 0;
        $rightSubTotal = 0;
    @endphp
    <p>
        <b>Date:</b>
        @if($from->toDateString() == $to->toDateString())
            {{ $to->format('d.m.Y') }}
        @else
           {{ $from->format('d.m.Y') }} To {{ $to->format('d.m.Y') }}
        @endif
    </p>
    <table>
        <thead>
            <tr>
                <th style="width: 60px;">SL</th>
                <th style="width: 200px;">Particulars</th>
                <th style="width: 120px;">Amount</th>

                <th style="width: 60px;">SL</th>
                <th style="width: 200px;">Particulars</th>
                <th style="width: 120px;">Amount</th>
            </tr>
        </thead>

        <tbody>

            @for($i = 0; $i < $half; $i++)

                @php
                    // Left
                    $left = $leftItems[$i] ?? null;
                    if ($left) {
                        if ($left->id === 'supplierbill') {
                            $leftTotal = $supplierBill; // use the pre-calculated custom value
                        } else {
                            $leftTotal = $expenses->where('category_id', $left->id)->sum('amount');
                        }
                        $leftSubTotal += $leftTotal;
                    } else {
                        $leftTotal = 0;
                    }

                    // Right side
                    $right = $rightItems[$i] ?? null;
                    if ($right) {
                        if ($right->id === 'supplierbill') {
                            $rightTotal = $supplierBill; // custom value for Creditor Bill
                        } else {
                            $rightTotal = $expenses->where('category_id', $right->id)->sum('amount');
                        }
                        $rightSubTotal += $rightTotal;
                    } else {
                        $rightTotal = 0;
                    }
                @endphp

                <tr>
                    {{-- LEFT --}}
                    <td>{{ $left ? ($i+1) : '' }}</td>
                    <td>{!! $left ? nl2br(e($left->name)) : '' !!}</td>
                    <td class="text-end">{{ $left ? numberFormat($leftTotal,2) : '' }}</td>

                    {{-- RIGHT --}}
                    <td>
                        @if($right)
                            {{ $i + 1 + $half }}
                        @endif
                    </td>
                    <td>{!! $right ? nl2br(e($right->name)) : '' !!}</td>
                    <td class="text-end">{{ $right ? numberFormat($rightTotal,2) : '' }}</td>
                </tr>

            @endfor

        </tbody>

        <tfoot>
            <tr>
                <th></th>
                <th class="text-end">Sub Total #</th>
                <th class="text-end">{{ numberFormat($leftSubTotal,2) }}</th>

                <th></th>
                <th class="text-end">Sub Total #</th>
                <th class="text-end">{{ numberFormat($rightSubTotal,2) }}</th>
            </tr>

            <tr>
                <th colspan="6" style="text-align: center">
                    Grand Total # {{ numberFormat($leftSubTotal + $rightSubTotal,2) }}
                </th>
            </tr>
            <tr>
                <th colspan="6" style="text-align: center">
                    <input type="hidden" name="total_amount_input" id="total_amount_input" value="{{ $leftSubTotal + $rightSubTotal }}">
                    In Words - Total Amount (Tk) : <span id="total_amount_word"></span>
                </th>
            </tr>
        </tfoot>
    </table>


    @else
    <span>No Report Data Found</span>
    @endif


    @endsection

    @php
        $signatures = ['Accounts Officer', 'Accounts Manager', 'Managing Director'];
    @endphp

    @push('js')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="{{asset('admin/assets/js/inword.js')}}"></script>
        <script>
            var amount = Number($('#total_amount_input').val());
            var words = toWords(amount);
            $('#total_amount_word').html(words + ' Taka Only');

        </script>
    @endpush



