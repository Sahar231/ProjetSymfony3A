package edu.connexion3a36.Controller;

import edu.connexion3a36.entities.Resultat;
import edu.connexion3a36.services.ResultatService;
import javafx.fxml.FXML;
import javafx.scene.control.TextField;

import java.sql.SQLException;
import java.time.LocalDateTime;
import java.time.format.DateTimeParseException;

public class AjouterResultatController {

    @FXML
    private TextField scoreTF;

    @FXML
    private TextField createdAtTF; // optional, ISO_LOCAL_DATE_TIME

    private final ResultatService resultatService = new ResultatService();

    @FXML
    public void ajouterResultat() {
        if (!ControllerUtils.isDouble(scoreTF)) {
            ControllerUtils.showError("Le score doit être un nombre valide.");
            return;
        }

        double score = Double.parseDouble(scoreTF.getText().trim());
        LocalDateTime createdAt = null;
        String createdText = createdAtTF.getText().trim();
        if (!createdText.isEmpty()) {
            try {
                createdAt = LocalDateTime.parse(createdText);
            } catch (DateTimeParseException e) {
                ControllerUtils.showError("Format de date invalide. Utilisez ISO_LOCAL_DATE_TIME (ex: 2023-05-01T14:30:00). ");
                return;
            }
        } else {
            createdAt = LocalDateTime.now();
        }

        Resultat r = new Resultat(score, createdAt);
        try {
            resultatService.ajouterResultat(r);
            ControllerUtils.showInfo("Résultat ajouté avec succès.");
            // Retour à la liste avec CSS conservé
            ControllerUtils.navigateTo(scoreTF, "/resultat_list.fxml");
        } catch (SQLException e) {
            ControllerUtils.showError("Erreur lors de l'ajout du résultat : " + e.getMessage());
        }
    }

    private void clearFields() {
        scoreTF.clear();
        createdAtTF.clear();
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
