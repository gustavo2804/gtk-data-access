function addHiddenInput(formId, inputName, inputValue) {
  const form = document.getElementById(formId);
  const hiddenInput = document.createElement("input");

  hiddenInput.type = "hidden";
  hiddenInput.name = inputName;
  hiddenInput.value = inputValue;

  form.appendChild(hiddenInput);
}

function fillForm(formId, formData) {
    const form = document.getElementById(formId);
    
    if (!form) {
      alert(`Form with ID '${formId}' not found.`);
      return; // Exit the function if form is not found
    }
  
    for (const key in formData) {
      if (formData.hasOwnProperty(key)) {
        const value = formData[key];
        const input = form.elements[key];
        if (input) {
          input.value = value;
        } else {
          alert(`Form field '${key}' does not exist.`);
          return; // Exit the function if a form field is missing
        }
      }
    }

    return form;
}

function fillAndSubmitForm(formId, formData) {

    var form = fillForm(formId, formData);
    
    var checkBox = document.getElementById('submitFormCheckbox');

    if (checkBox.checked)
    {
        form.submit();
    }
}
  
function prepareScenarioButtons(scenarios, targetElementId) {
    const scenarioButtons = document.getElementById(targetElementId);
    
    const submitLabel     = document.createElement('label');
    submitLabel.for       = 'submitFormCheckbox';
    submitLabel.innerHTML = 'Submit Form?';

    const submitCheckbox   = document.createElement('input');
    submitCheckbox.id      = 'submitFormCheckbox';
    submitCheckbox.type    = 'checkbox';
    submitCheckbox.checked = true;

    scenarioButtons.appendChild(submitLabel);
    scenarioButtons.appendChild(submitCheckbox);
  
    scenarios.forEach(function(scenario) {
      const button = createScenarioButton(scenario);
      scenarioButtons.appendChild(button);
    });


}

function createScenarioButton(scenario) {
  const scenarioName = scenario.name;
  const formId       = scenario.formId;
  const formData     = scenario.formData;
  const button = document.createElement('button');

  button.textContent = scenarioName;
  button.style.width = '88px';
  button.style.height = '54px';
  button.style.margin = '4px';
  
  button.addEventListener('click', async function() {

    if (scenario.beforeSubmit) 
    {
      for (let activity of scenario.beforeSubmit) 
      {
          
          
          var result = null;
          var toCall = activity.fn;

          console.log("Activity: " + activity);
          console.log("Will call: " + toCall);


          try
          {
            if (activity.args) {
              console.log("Calling with args: " + activity.args);
              result = await toCall(...activity.args);
              console.log("Inner result: " + await result);
          } else {
              result = await toCall();
          }

          console.log("Inner result: " + await result);
          console.log("Got result: " + result);
          // If a function in beforeSubmit returns a result, you can handle it here.
          // For the current context, I assume a result modifies formData (like your original code)
          if (result && activity.modifiesFormData) {
              result.forEach(element => {
                  let key   = element[0];
                  let value = element[1];
                  console.log("Got key: " + key);
                  console.log("Got value: " + value);
                  formData[key] = value;
              });
          }
          }
          catch (err)
          {
              console.log("Error working with activity: " + activity);
          }

        


      }
    } 
    
    
    fillAndSubmitForm(formId, formData);
        
    
  });

  return button;
}
  
  
  function insertStickyFooterWithId(targetElementId) {
    const footer = document.createElement('footer');
    footer.setAttribute('id', targetElementId); 
    document.body.appendChild(footer);
  }

  var scenarios = [];