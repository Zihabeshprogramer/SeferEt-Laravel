<!-- Confirmation Modal -->
<div class="modal fade" id="confirmBookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Booking</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="confirmBookingForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="internal_notes">Internal Notes (Optional)</label>
                        <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="send_confirmation_email" 
                                   name="send_confirmation_email" checked>
                            <label class="form-check-label" for="send_confirmation_email">
                                Send confirmation email to customer
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancellation Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Booking</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="cancelBookingForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cancellation_reason">Cancellation Reason *</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                  rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="refund_amount">Refund Amount (Optional)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                                   step="0.01" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="notify_customer" 
                                   name="notify_customer" checked>
                            <label class="form-check-label" for="notify_customer">
                                Notify customer about cancellation
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Update Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Status</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updatePaymentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="payment_status">Payment Status *</label>
                        <select class="form-control" id="payment_status" name="payment_status" required>
                            @foreach(\App\Models\PackageBooking::PAYMENT_STATUSES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paid_amount">Paid Amount *</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="paid_amount" name="paid_amount" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="">Select Method</option>
                            @foreach(\App\Models\PackageBooking::PAYMENT_METHODS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payment_notes">Payment Notes</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
