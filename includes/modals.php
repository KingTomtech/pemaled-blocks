 <!-- Profile Modal -->
 <div
      class="modal fade"
      id="profileModal"
      tabindex="-1"
      aria-labelledby="profileModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="profileModalLabel">Profile Settings</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">Dashboard Customization</label>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="showProduction"
                    checked
                  />
                  <label class="form-check-label" for="showProduction"
                    >Show Production Card</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="showOrders"
                    checked
                  />
                  <label class="form-check-label" for="showOrders"
                    >Show Orders Card</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="showFinancials"
                    checked
                  />
                  <label class="form-check-label" for="showFinancials"
                    >Show Financials Card</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="showInventory"
                    checked
                  />
                  <label class="form-check-label" for="showInventory"
                    >Show Inventory Card</label
                  >
                </div>
              </div>
              <div class="mb-3">
                <label for="chartTimeframe" class="form-label"
                  >Chart Timeframe</label
                >
                <select class="form-select" id="chartTimeframe">
                  <option value="weekly">Weekly</option>
                  <option value="monthly" selected>Monthly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button type="button" class="btn btn-primary" id="saveSettings">
              Save changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Production Modal -->
    <div
      class="modal fade"
      id="productionModal"
      tabindex="-1"
      aria-labelledby="productionModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="productionModalLabel">
              Update Production
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form id="productionForm">
              <div class="mb-3">
                <label for="blocks6" class="form-label">6" Blocks</label>
                <input
                  type="number"
                  class="form-control"
                  id="blocks6"
                  min="0"
                />
              </div>
              <div class="mb-3">
                <label for="blocks4" class="form-label">4" Blocks</label>
                <input
                  type="number"
                  class="form-control"
                  id="blocks4"
                  min="0"
                />
              </div>
              <div
                id="productionError"
                class="text-danger"
                style="display: none"
              >
                Please enter valid quantities.
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button type="button" class="btn btn-primary" id="saveProduction">
              Save changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Modal -->
    <div
      class="modal fade"
      id="inventoryModal"
      tabindex="-1"
      aria-labelledby="inventoryModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="inventoryModalLabel">
              Manage Inventory
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form id="inventoryForm">
              <div class="mb-3">
                <label for="cement" class="form-label">Cement (Bags)</label>
                <input type="number" class="form-control" id="cement" min="0" />
              </div>
              <div class="mb-3">
                <label for="stone" class="form-label">Stone (Tons)</label>
                <input type="number" class="form-control" id="stone" min="0" />
              </div>
              <div class="mb-3">
                <label for="diesel" class="form-label">Diesel (Litres)</label>
                <input type="number" class="form-control" id="diesel" min="0" />
              </div>
              <div
                id="inventoryError"
                class="text-danger"
                style="display: none"
              >
                Please enter valid quantities.
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button type="button" class="btn btn-primary" id="saveInventory">
              Save changes
            </button>
          </div>
        </div>
      </div>
    </div>
