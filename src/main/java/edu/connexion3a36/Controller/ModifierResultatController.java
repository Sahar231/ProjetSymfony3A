package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Resultat;
import edu.connexion3a36.services.ResultatService;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.TextField;
import javafx.scene.control.cell.PropertyValueFactory;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.time.format.DateTimeParseException;

public class ModifierResultatController {

    @FXML
    private TableView<Resultat> resultatTable;

    @FXML
    private TableColumn<Resultat, Integer> idCol;

    @FXML
    private TableColumn<Resultat, Double> scoreCol;

    @FXML
    private TableColumn<Resultat, String> createdAtCol;

    @FXML
    private TextField scoreTF;

    @FXML
    private TextField createdAtTF;

    private final ResultatService resultatService = new ResultatService();
    private Resultat selectedResultat;

    @FXML
    public void initialize() {
        idCol.setCellValueFactory(new PropertyValueFactory<>("idResultat"));
        scoreCol.setCellValueFactory(new PropertyValueFactory<>("score"));
        createdAtCol.setCellValueFactory(new PropertyValueFactory<>("createdAt"));
        chargerResultats();
        resultatTable.getSelectionModel().selectedItemProperty().addListener((obs, oldVal, newVal) -> setResultat(newVal));
    }

    public void setResultat(Resultat r) {
        this.selectedResultat = r;
        if (r != null) {
            scoreTF.setText(String.valueOf(r.getScore()));
            createdAtTF.setText(r.getCreatedAt() != null ? r.getCreatedAt().toString() : "");
        }
    }

    @FXML
    public void modifierResultat() {
        if (selectedResultat == null) { ControllerUtils.showWarning("Sélectionnez un résultat à modifier."); return; }
        if (!ControllerUtils.isDouble(scoreTF)) { ControllerUtils.showError("Le score doit être un nombre valide."); return; }
        double score = Double.parseDouble(scoreTF.getText().trim());
        LocalDateTime createdAt = null;
        String createdText = createdAtTF.getText().trim();
        if (!createdText.isEmpty()) {
            try { createdAt = LocalDateTime.parse(createdText); } catch (DateTimeParseException e) { ControllerUtils.showError("Format de date invalide."); return; }
        }
        selectedResultat.setScore(score);
        selectedResultat.setCreatedAt(createdAt);
        try {
            resultatService.modifierResultat(selectedResultat);
            ControllerUtils.showInfo("Résultat modifié.");
            chargerResultats();
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de la modification : " + e.getMessage());
        }
    }

    private void chargerResultats() {
        try { resultatTable.setItems(FXCollections.observableArrayList(resultatService.afficherResultat())); }
        catch (SQLException e) { ControllerUtils.showError("Erreur lors du chargement des résultats : " + e.getMessage()); }
    }

    @FXML
    private void handleBack(javafx.event.ActionEvent event) {
        try {
            javafx.fxml.FXMLLoader loader = new javafx.fxml.FXMLLoader(getClass().getResource("/home.fxml"));
            javafx.scene.Parent root = loader.load();
            javafx.scene.Node source = (javafx.scene.Node) event.getSource();
            javafx.stage.Stage stage = (javafx.stage.Stage) source.getScene().getWindow();
            javafx.scene.Scene scene = stage.getScene();
            if (scene == null) {
                javafx.scene.Scene newScene = new javafx.scene.Scene(root, 1100, 700);
                newScene.getStylesheets().add(getClass().getResource("/style.css").toExternalForm());
                stage.setScene(newScene);
            } else {
                scene.setRoot(root);
            }
        } catch (java.io.IOException e) {
            ControllerUtils.showError("Impossible de retourner à l'accueil: " + e.getMessage());
        }
    }
}
