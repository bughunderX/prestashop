document.addEventListener("DOMContentLoaded", function () {
    const shippingTable = document.getElementById("shipping-rules-table");
    const idProduct = shippingTable.dataset.idProduct;

    console.log("Product ID:", idProduct);
    // Add New Rule
    const addNewRuleButton = document.getElementById("add-new-rule");
    const shippingTableBody = document.querySelector("#shipping-rules-table tbody");

    addNewRuleButton.addEventListener("click", function (e) {
        e.preventDefault();
        const newCountrySelect = document.querySelector(".new-shipping-country");
        const newCountry = newCountrySelect.value;
        const newCountryName = newCountrySelect.options[newCountrySelect.selectedIndex].text; // Get full country name
        
        const newStartRate = document.querySelector(".new-shipping-start-rate").value;
        const newExtraRate = document.querySelector(".new-shipping-extra-rate").value;
        alert("Add11 action.");

        if (!newCountry || !newStartRate || !newExtraRate) {
            alert("Please fill in all fields before adding a new rule.");
            return;
        }

        // Send data via AJAX
        fetch(addShippingRuleUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                action: "AddShippingRule",
                id_product: idProduct,
                country: newCountry,
                start_rate: newStartRate,
                extra_rate: newExtraRate,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("New rule added successfully!");
                    // location.reload(); // Refresh to update table
                    // Add the new row to the table
                    const newRow = document.createElement("tr");
                    newRow.setAttribute("data-id", idProduct);
                    newRow.setAttribute("data-country", newCountry);
                    newRow.innerHTML = `
                        <td>
                            <select class="form-control shipping-country" disabled>
                                <option value="${newCountry}" selected>${newCountryName}</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control shipping-start-rate" value="${newStartRate}">
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control shipping-extra-rate" value="${newExtraRate}">
                        </td>
                        <td>
                            <button class="btn btn-success update-row"><i class="icon-check"></i></button>
                            <button class="btn btn-danger delete-row"><i class="icon-trash"></i></button>
                        </td>
                    `;

                    shippingTableBody.appendChild(newRow);
                    console.log(newRow);

                    // Reset the new rule fields for another addition
                    document.querySelector(".new-shipping-country").value = "";
                    document.querySelector(".new-shipping-start-rate").value = "";
                    document.querySelector(".new-shipping-extra-rate").value = "";
                } else {
                    alert("Error adding rule: " + data.message);
                }
            })
            .catch((error) => console.error("Error:", error));
    });

    // Update Rule
    shippingTable.addEventListener("click", function (e) {
        e.preventDefault();
        if (e.target.closest(".update-row")) {
            const row = e.target.closest("tr");
            const productId = row.getAttribute("data-id");
            const country = row.getAttribute("data-country");
            const startRate = row.querySelector(".shipping-start-rate").value;
            const extraRate = row.querySelector(".shipping-extra-rate").value;
            alert("Update action.");

            if (!startRate || !extraRate) {
                alert("Start rate and extra rate must be provided.");
                return;
            }

            // Send data via AJAX
            fetch(updateShippingRuleUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    action: "UpdateShippingRule",
                    id_product: productId,
                    shipping_country: country,
                    start_rate: startRate,
                    extra_rate: extraRate,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Rule updated successfully!");
                    } else {
                        alert("Error updating rule: " + data.message);
                    }
                })
                .catch((error) => console.error("Error:", error));
        }
    });

    // Delete Rule
    shippingTable.addEventListener("click", function (e) {
        e.preventDefault();
        if (e.target.closest(".delete-row")) {
            const row = e.target.closest("tr");
            const productId = row.getAttribute("data-id");
            const country = row.getAttribute("data-country");
            alert("Delete action.");

            if (!confirm("Are you sure you want to delete this rule?")) {
                return;
            }

            // Send data via AJAX
            fetch(deleteShippingRuleUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    action: "DeleteShippingRule",
                    id_product: productId,
                    shipping_country: country,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Rule deleted successfully!");
                        row.remove(); // Remove row from table
                    } else {
                        alert("Error deleting rule: " + data.message);
                    }
                })
                .catch((error) => console.error("Error:", error));
        }
    });
});
